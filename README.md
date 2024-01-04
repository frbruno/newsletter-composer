SEINE: Image-to-Video generation
SEINE: Short-to-Long Video Diffusion Model for Generative Transition and Prediction

https://colab.research.google.com/drive/1ONZJKsk6i1I2chn-WA8wKTtC90I7aaa9#scrollTo=VjYy0F2gZIPR

%cd /content
!git clone -b dev https://github.com/camenduru/SEINE-hf
%cd /content/SEINE-hf

!apt -y install -qq aria2
!aria2c --console-log-level=error -c -x 16 -s 16 -k 1M https://huggingface.co/vdo/SEINE/resolve/main/pre-trained/seine.pt -d /content/SEINE-hf/pre-trained -o seine.pt
!aria2c --console-log-level=error -c -x 16 -s 16 -k 1M https://huggingface.co/vdo/SEINE/resolve/main/pre-trained/stable-diffusion-v1-4/text_encoder/pytorch_model.bin -d /content/SEINE-hf/pre-trained/stable-diffusion-v1-4/text_encoder -o pytorch_model.bin
!aria2c --console-log-level=error -c -x 16 -s 16 -k 1M https://huggingface.co/vdo/SEINE/resolve/main/pre-trained/stable-diffusion-v1-4/vae/diffusion_pytorch_model.bin -d /content/SEINE-hf/pre-trained/stable-diffusion-v1-4/vae -o diffusion_pytorch_model.bin
!aria2c --console-log-level=error -c -x 16 -s 16 -k 1M https://huggingface.co/vdo/SEINE/resolve/main/input/transition/1/2-Wide%20angle%20shot%20of%20an%20alien%20planet%20with%20cherry%20blossom%20forest-2.png -d /content/SEINE-hf/input/transition/1 -o 2-Wide%20angle%20shot%20of%20an%20alien%20planet%20with%20cherry%20blossom%20forest-2.png

!pip install -q gradio==3.50.2 einops diffusers accelerate rotary_embedding_torch omegaconf av python-docx
!pip install -q https://download.pytorch.org/whl/cu121/xformers-0.0.22.post7-cp310-cp310-manylinux2014_x86_64.whl

!python app.py
