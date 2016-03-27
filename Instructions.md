#Instructions for using Newsletter Composer

# Newsletter Composer Instructions #

## Overview ##

This is designed the greatly ease the process of sending out regular newsletters as emails with embedded HTML (this way the recipient can read your nicely formatted newsletter without having to open any attachments).

This is the process:

  1. You type the content of your newsletter into the online form.
  1. This content is used with a template to automatically generate two HTML files which you can look at:
    * One to be sent as an email
    * One to be hosted online
> (The email links to the online one, for people having trouble viewing the email)
  1. You input the names and email addresses of recipients or import them from a spreadsheeet, adding any personal greetings to individuals that you want.
  1. The email gets automatically personalised for each individual and sent to them using a mail server that you specify.
Note: Newsletter Composer does not work in Internet Explorer Try downloading a stable secure standards-compliant browser such as Chrome or Firefox

## Personal Details ##

The first section of the form is for details about you and your organisation. These are optional and can be left blank. If you do choose to include them they may be inserted into the headers or footers of the HTML files that are generated. The template decides which details should be inserted into particular files. For example the Cool template will put your postal address and phone number(s) into the footer of the email, but not into the web version.

All content entered here is remembered by the browser for up to one year.

## Newsletter Content ##

The second part of the form is where you enter content for your newsletter.

The clear button allows you to clear all existing content.

Currently there is only one template available, which determines the appearance of the generated newsletter.

The subscribe and unsubscribe addresses allow you to direct the reader to somewhere they can sign up or unsubscribe from your newsletter. The subscribe link will be used in the online version, and the unsubscribe in the email. It is easy with a Google account to set up a form in Google Docs that lets people enter information that gets dropped into a spreadsheet that you can see. This is one simple way to set up online subscribe and unsubscribe forms.

The newsletter is arranged in two columns. To add an article to the left column use the left-hand Add Article button and to add an article to the right column use the right-hand one.

once an article is added four buttons appear within the article. These buttons can be used to add any number of titles, paragraphs, list items or images to the articles.

Items added to an article can be moved up or down with relation to each other or deleted using the blue controls that appear to the top-right of the item. There are also controls that allow the same actions for whole articles too.

Large images are automatically resized to a width of 600 pixels before they are uploaded to the server. After they are uploaded you can see a preview at 200 pixels.

All content entered here is remembered by the browser for up to one year, so you can leave the page and return again without loosing your content. If you return to the page from a different computer or browser your content will not be restored. However there is the ability to save the content of the newsletter into a file using the Save button, and to load it from that file using the Load button. In this way to form content can be transfered between computers.

## Generate Newsletter ##

Once you have finished inputing the content of the newsletter use the Generate Newsletter button to generate the HTML files. One is for hosting online, another is to be emailed to your recipients. Soon there will be a third file for printing, but this feature is not implemented yet.

When the files are generated links to them will appear below the button so you can preview them. Make any changes you want and generate them again.

Once the files are generated they exist on the server and can be viewed by anyone. Regenerating the newsletter Overwrites the existing files, except when you change the name or issue number of the newsletter, then new files are created.

Whenever the page is refreshed the links to the generated files disappears, but the files are still there. Regenerate to get the links back.

## Sending the Newsletter ##

You can enter the recipients of the newsletter manually. Enter the namea nd email address of a recipient. When you start typing a nother row appears below for adding more.

Each newsletter that get sent out is personalised. It is addressed to the person by name. There is a button next to each person's entry field that allows you to add a personal greeting that appears as thefirst line in the header of the newsletter just below the person's name. If nothing is entered here then the greeting will be the generic one you enter above the table of recipients.

It is also possible to import recipients from an Excel spreadsheet. To do this use the controls below the recipients table. Choose a file to import and click Go. The format of the spreadsheet is as follows:

|Email|Dear|Name|
|:----|:---|:---|
|email@example.com|Jonny|John Smith|
|another.email@sample.org|    |Jane W. Doe|
This file format matches that format exported from Donor Manger when you choose to make a spreadsheet of emails. The name for the recipient is taken from the Dear field, but if this field is empty the first word from the name field is used instead.

When you import recipients the page refreshes. This means you will have to press the Generate Newsletter button again before you can send.

The imported recipients appear before the Existing table entries.

Below the recipients enter the email address that the email will appear to come from and the subject line for the email.

Finally you must specify a mail server to deliver the emails. You can find the information to put in these fields from whoever manages your emails. You just need the outgoing mail (SMTP) settings. For example, if you want to use your gmail account to send out the emails you can find the [relevant information](http://support.google.com/mail/bin/answer.py?hl=en&answer=13287) from Google.

Put the protocol name such as ssl in front of the SMTP host name like this `ssl://smtp.gmail.com`

Click Send to send your email to all the recipients. If this button is greyed out try regenerating the newsletter. The emails will be sent individually at one second intervals and a list of the results will begin appearing to the right. If any email fails to send you will get a Fail message next to that person's name. If, once all the emails are sent, just a few have failed, then clear all recipients except for those few and try resending. If all the emails failed then you have a problem that an IT technician needs to sort out for you.