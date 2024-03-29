<?php

require_once 'db.php';
require_once 'common.php';

// check user is logged in
if (login_ok() == 1) {
	
	//echo "UID: {$_SESSION['uid']} ";
	//echo "title: {$_POST['title']} ";
	
	$dbh = dbConnect();
	
	// get db record id for this user by querying using the PHP session uid
	$q_get_uid = $dbh->prepare("SELECT id FROM Users WHERE name=:user_name");
	$q_get_uid->bindParam(':user_name', $_SESSION['uid']);
	$q_get_uid->execute();
	$db_uid_row = $q_get_uid->fetch();
	$db_uid = $db_uid_row['id'];
	
	//echo "<p>db uid: {$db_uid}</p>";
	//echo "<p>posting nl id: {$_POST['newsletter_id']}</p>";
	
	$current_newsletter_id  = null;
	if (isset($_POST['newsletter_id']))
	{
		//echo "<p>checking valid nl id: {$_POST['newsletter_id']}</p>";
		// if a newsletter id is fed in we have to check it exists and belongs to the current user
		$q_check_id = $dbh->prepare("SELECT * FROM Newsletters WHERE id = :id and user = :user");
		$q_check_id->bindParam(':id', $_POST['newsletter_id']);
		$q_check_id->bindParam(':user', $db_uid);
		$q_check_id->execute();
		if ($q_check_id->rowCount() > 0)
		{
			$current_newsletter_id = $_POST['newsletter_id'];
			//echo "<p>setting nl id: {$current_newsletter_id}</p>";
		}
	}
	
	function getCurrentNewsletterID($dbh, $db_uid, &$current_newsletter_id)
	{
		// if we don't know which is the current newsletter assume it's the most recent
		if ($current_newsletter_id == null)
		{
			$q_recent_newsletter_id = $dbh->query("SELECT id FROM Newsletters WHERE user = " . $db_uid . " AND `timestamp` = (SELECT MAX(`timestamp`) FROM Newsletters WHERE user = " . $db_uid . ")");
			$recent_newsletter_id = $q_recent_newsletter_id->fetchAll(PDO::FETCH_ASSOC);
			
			if (sizeof($recent_newsletter_id) > 0)
			{
				$current_newsletter_id = $recent_newsletter_id[0]['id'];
			}
			// if no results returned then there are no newsletters yet.
			// in this case we'll leave the newsletter id as null
			$q_recent_newsletter_id->closeCursor();
		}
		return $current_newsletter_id;
	}
	
	//$tmp_id = getCurrentNewsletterID($dbh, $db_uid, $current_newsletter_id);
	//echo "<p>getting current nl id: {$tmp_id}</p>";
	
	// the content of a newsletter needs to have a title and issue added
	// if these are already there they could be erroneous and the values
	// passed to this function should be used instead
	function fixContent($json_content, $title, $issue)
	{
		$content = json_decode($json_content, true);
		$content['title'] = $title;
		$content['number'] = $issue;
		return json_encode($content);
	}
	
	if (strcmp($_POST['task'], 'get_newsletter_id') == 0)
	{
		$id = getCurrentNewsletterID($dbh, $db_uid, $current_newsletter_id);
		if ($id == null) echo "No newsletters";
		else echo $id;
	}
	
	else if (strcmp($_POST['task'], 'save') == 0)
	{		
		// create a new entry in the NewsletterSaves table
		$q_save_newsletter = $dbh->prepare("INSERT INTO NewsletterSaves (newsletter, content) VALUES (:newsletter_id,:content)");
		$q_save_newsletter->bindParam(':newsletter_id', getCurrentNewsletterID($dbh, $db_uid, $current_newsletter_id));
		$q_save_newsletter->bindParam(':content', json_encode($_POST['content']));
		//$q_save_newsletter->debugDumpParams();
		$q_save_newsletter->execute();
		$save_affected = $q_save_newsletter->rowCount();
		if ($save_affected == 1)
		{
			// return the datetime of the successful save in UTC
			echo gmdate('Y-m-d H:i:s'), ' UTC';
		}
		else
		{
			echo "Save Failed: $save_affected records made/modified (expecting 1)";
		}
	}
	
	else if (strcmp($_POST['task'], 'autosave') == 0)
	{
		// this is to happen after every change of the newsletter to keep the history table up to date
		// first copy the current content into the history table
		$dbh->query("INSERT INTO NewsletterHistory (newsletter, content) SELECT id, content FROM Newsletters WHERE Newsletters.id = " . getCurrentNewsletterID($dbh, $db_uid, $current_newsletter_id));
		// now update the content in the current newsletter
		$q_save_newsletter = $dbh->prepare("UPDATE Newsletters SET content=:content WHERE id=:id");
		$q_save_newsletter->bindParam(':content', json_encode($_POST['content']));
		$q_save_newsletter->bindParam(':id', getCurrentNewsletterID($dbh, $db_uid, $current_newsletter_id));
		$q_save_newsletter->execute();
		$save_affected = $q_save_newsletter->rowCount();
		if ($save_affected == 1)
		{
			//echo 'Newsletter saved';
			// return the datetime of the successful save in UTC
			echo gmdate('Y-m-d H:i:s'), ' UTC';
			
			// Finally delete entries for this newsletter from the future table for this user because we can't redo from here
			$dbh->query("DELETE FROM NewsletterFuture WHERE newsletter = " . getCurrentNewsletterID($dbh, $db_uid, $current_newsletter_id) . ')');
		}
		else
		{
			echo "Autosave Failed: $save_affected records made/modified (expecting 1)";
		}
	}
	
	else if (strcmp($_POST['task'], 'get_all_saves') == 0)
	{
		// this will get a list of ids and dates for all saved instances of a given newsletter issue
		$q_get_revisions = $dbh->prepare("SELECT id,timestamp FROM NewsletterSaves WHERE newsletter=:newsletter_id ORDER BY timestamp DESC");
		$q_get_revisions->bindParam(':newsletter_id', getCurrentNewsletterID($dbh, $db_uid, $current_newsletter_id));
		$q_get_revisions->execute();
		if ($q_get_revisions->rowCount() > 0)
		{
			$revisions = $q_get_revisions->fetchAll(PDO::FETCH_ASSOC);
			// convert all the dates to UTC
			foreach ($revisions as $rev_i => $revision)
			{
				$revisions[$rev_i]['timestamp'] = gmdate('Y-m-d H:i:s', date_timestamp_get(date_create($revisions[$rev_i]['timestamp']))) . ' UTC';
			}
			echo json_encode($revisions);
		}
		else
		{
			// do nothing if there is no data to load from db
			// The js that calls this file will get back an empty string
		}
	}
	
	else if (strcmp($_POST['task'], 'restore') == 0)
	{
		// get the saved record of the current newsletter
		$q_get_newsletter = $dbh->prepare("SELECT * FROM Newsletters WHERE id=:id");
		$q_get_newsletter->bindParam(':id', getCurrentNewsletterID($dbh, $db_uid, $current_newsletter_id));
		$q_get_newsletter->execute();
		$newsletter = $q_get_newsletter->fetchAll(PDO::FETCH_ASSOC);
		
		if (sizeof($newsletter) > 0)
		{		
			echo fixContent($newsletter[0]['content'], $newsletter[0]['name'], $newsletter[0]['issue']);
		}
		else
		{
			// do nothing if there is no data to load from db
			// The js that calls this file will get back an empty string
		}
	}
	
	else if (strcmp($_POST['task'], 'undo') == 0)
	{
		// get the latest entry for the current newsletter from the history table
		getCurrentNewsletterID($dbh, $db_uid, $current_newsletter_id);
		$q_latest_history = $dbh->query("SELECT * FROM NewsletterHistory WHERE newsletter = " . $current_newsletter_id . " AND `timestamp` = (SELECT MAX(`timestamp`) FROM NewsletterHistory WHERE newsletter = " . $current_newsletter_id .")");
		if ($q_latest_history->rowCount() == 1)
		{
			// store the current content into the future table
			$dbh->query("INSERT INTO NewsletterFuture (newsletter, content) SELECT id, content FROM Newsletters WHERE Newsletters.id = " . $current_newsletter_id);
			// update the newsletter table with the data from history
			$latest_history = $q_latest_history->fetchAll(PDO::FETCH_ASSOC);
			//echo "history id: " . $latest_history[0]['id'];
			//echo "\n\nhistory content: " . $latest_history[0]['content'];
			$q_save_newsletter = $dbh->prepare("UPDATE Newsletters SET content=:content WHERE id=:id");
			$q_save_newsletter->bindParam(':content', $latest_history[0]['content']);
			$q_save_newsletter->bindParam(':id', $current_newsletter_id);
			$q_save_newsletter->execute();
			// delete the record we used from the history table
			$dbh->query("DELETE FROM NewsletterHistory WHERE id = " . $latest_history[0]['id']);
		}
		else
		{
			echo 'no data';
		}
	}
	
	else if (strcmp($_POST['task'], 'redo') == 0)
	{
		// get the latest entry for the current newsletter from the future table
		getCurrentNewsletterID($dbh, $db_uid, $current_newsletter_id);
		$q_latest_future = $dbh->query("SELECT * FROM NewsletterFuture WHERE newsletter = " . $current_newsletter_id . " AND `timestamp` = (SELECT MAX(`timestamp`) FROM NewsletterFuture WHERE newsletter = " . $current_newsletter_id .")");
		if ($q_latest_future->rowCount() == 1)
		{
			// store the current content into the history table
			$dbh->query("INSERT INTO NewsletterHistory (newsletter, content) SELECT id, content FROM Newsletters WHERE Newsletters.id = " . $current_newsletter_id);
			// update the newsletter table with the data from future
			$latest_future = $q_latest_future->fetchAll(PDO::FETCH_ASSOC);
			$q_save_newsletter = $dbh->prepare("UPDATE Newsletters SET content=:content WHERE id=:id");
			$q_save_newsletter->bindParam(':content', $latest_future[0]['content']);
			$q_save_newsletter->bindParam(':id', $current_newsletter_id);
			$q_save_newsletter->execute();
			// delete the record we used from the future table
			$dbh->query("DELETE FROM NewsletterFuture WHERE id = " . $latest_future[0]['id']);
		}
		else
		{
			echo 'no data';
		}
	}
	
	else if (strcmp($_POST['task'], 'new_newsletter') == 0)
	{
		// first get the content from the newsletter we are copying from
		// if the id < 0 no newsletter is selected so we wont copy content
		if ((int)$_POST['content_from'] < 0)
		{
			// make blank content
			$new_content = array();
			$new_content['template'] = 'cool';
			$new_content['logo'] = '';
			$new_content['mugshot'] = '';
			$new_content['header'] = array('email' => '', 'web' => '', 'print' => '');
			$new_content['footer'] = array('email' => '', 'web' => '', 'print' => '');
			$new_content['mainArticles'] = array();
			$new_content['sideArticles'] = array();
		}
		else
		{
			// get the content from the selected newsletter

			// remove any content that was not selected to be copied

		}
		// set the date to be the current month

		// create then new newsletter

		// output the newsletter content
	}
	
	else if (strcmp($_POST['task'], 'change_newsletter') == 0)
	{
		$q_find_newsletter = $dbh->prepare("SELECT id FROM Newsletters WHERE user=:user AND name=:title AND issue=:issue");
		$q_find_newsletter->bindParam(':user', $db_uid);
		$q_find_newsletter->bindParam(':title', $_POST['newsletter_title']);
		$q_find_newsletter->bindParam(':issue', $_POST['newsletter_issue']);
		$q_find_newsletter->execute();
		// if the newsletter doesn't exist add it
		if ($q_find_newsletter->rowCount() == 0)
		{
			$q_new_newsletter = $dbh->prepare("INSERT INTO Newsletters (user, name, issue, content) VALUES (:user, :title, :issue, :content)");
			$q_new_newsletter->bindParam(':user', $db_uid);
			$q_new_newsletter->bindParam(':title', $_POST['newsletter_title']);
			$q_new_newsletter->bindParam(':issue', $_POST['newsletter_issue']);
			$q_new_newsletter->bindParam(':content', json_encode($_POST['content']));
			$q_new_newsletter->execute();
			// now we can try that original query again.
			$q_find_newsletter->execute();
		}
		
		$found_newsletter = $q_find_newsletter->fetchAll(PDO::FETCH_ASSOC);
		echo $found_newsletter[0]['id'];
		
	}
	
	else if (strcmp($_POST['task'], 'get_all_newsletters') == 0)
	{
		// this will get a list of ids and titles and issue numbers for all this user's newsletters
		$q_get_newsletters = $dbh->prepare("SELECT id, name, issue FROM Newsletters WHERE user=:user_id ORDER BY timestamp ASC");
		$q_get_newsletters->bindParam(':user_id', $db_uid);
		$q_get_newsletters->execute();
		if ($q_get_newsletters->rowCount() > 0)
		{
			$newsletters = $q_get_newsletters->fetchAll(PDO::FETCH_ASSOC);
			echo json_encode($newsletters);
		}
		else
		{
			// do nothing if there is no data to load from db
			// The js that calls this file will get back an empty string
		}
	}
	
	else if (strcmp($_POST['task'], 'delete_newsletter') == 0)
	{
		// This will genuinely delete a newsletter from the database.
		// The assumption is that tables are properly related in the backend
		// and that the delte will cascade over the saved instances.
		// It is only possible to restore a deleted newsletter if the server administrator makes regular backups of the db

		//error_reporting(E_ALL);
		//ini_set('display_errors', 1);
		$q_delete_newsletter = $dbh->prepare("DELETE FROM Newsletters WHERE id=:newsletter_id AND user=:user_id");
		$q_delete_newsletter->bindParam(':user_id', $db_uid);
		$q_delete_newsletter->bindParam(':newsletter_id', $_POST['newsletter_id']);
		$q_delete_newsletter->execute();
		echo $q_delete_newsletter->rowCount();
	}
	
	else if (strcmp($_POST['task'], 'clear_old_history') == 0)
	{
		// clear both the redo and undo history of entries older than a day
		$yesterday = strtotime('-1 day');
		$table = 'NewsletterHistory';
		$q_clear_table = $dbh->prepare("DELETE FROM :table WHERE timestamp<:expire AND newsletter IN (SELECT id FROM Newsletters WHERE user = " . getCurrentNewsletterID($dbh, $db_uid, $current_newsletter_id) . ')');
		$q_clear_table->bindParam(':expire', $yesterday);
		$q_clear_table->bindParam(':table', $table);
		$q_clear_table->execute();
		$table = 'NewsletterFuture';
		$q_clear_table->execute();
	}
	
	else
	{
		echo "<p class=\"error error_02\">Fail. unrecognised task: {$_POST['task']}</p>";
	}
	
	$dbh = null;
	
} else {
	echo '<p class="error error_01">Fail. Could not verify user. Login again.</p>';
}
