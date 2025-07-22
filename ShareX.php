<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="/logo.png">
  <title>ShareX Integration - hapka.lol</title>
  <style>
    body { background: #181a1b; color: #e3e3e3; font-family: 'Segoe UI', Arial, sans-serif; margin: 0; }
    .header-wrap {
      max-width: 600px;
      margin: 0 auto 0 auto;
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 18px 0 8px 0;
      margin-bottom: 0;
      background: transparent;
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
    }
    .header-left {
      display: flex;
      align-items: center;
      gap: 24px;
    }
    .logo {
      font-size: 2rem;
      font-weight: 700;
      letter-spacing: 1px;
      display: flex;
      align-items: center;
    }
    .logo-gradient {
      background: linear-gradient(90deg, #8f5cff 0%, #5865f2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      text-fill-color: transparent;
    }
    .header-right {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .header-link {
      color: #8ab4f8;
      text-decoration: none;
      font-size: 1rem;
      padding: 4px 10px;
      border-radius: 6px;
      transition: background 0.2s, color 0.2s;
    }
    .header-link:hover {
      background: #23272a;
      color: #fff;
    }
    .support-btn {
      background: #23272a;
      color: #fff;
      border-radius: 6px;
      padding: 4px 14px 4px 10px;
      font-size: 1rem;
      text-decoration: none;
      font-weight: 500;
      border: 1px solid #36393f;
      transition: background 0.2s, border 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }
    .support-btn:hover {
      background: #5865f2;
      border: 1px solid #5865f2;
      color: #fff;
    }
    .header-underline {
      height: 3px;
      background: linear-gradient(90deg, #5865f2 0%, #8ab4f8 100%);
      border-radius: 2px;
      margin-bottom: 30px;
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
    }
    .nav-dot {
      color: #5865f2;
      margin: 0 4px;
      font-size: 1.1em;
    }
    .container {
      max-width: 600px;
      margin: auto;
      margin-top: 54px;
      background: #202225;
      border-radius: 12px;
      border: 1px solid #23272a;
      box-shadow: none;
      padding: 18px 28px 24px 28px;
    }
    h1 { color: #8ab4f8; font-size: 2em; margin-top: 0; }
    h2 { color: #8ab4f8; margin-bottom: 8px; }
    pre { background: #181b20; padding: 12px 14px; border-radius: 8px; overflow-x: auto; font-size: 1em; color: #b6e1ff; }
    .warn { color: #ffb4b4; font-size: 1em; margin-top: 12px; }
    .api-key { color: #fff; background: #181b20; padding: 4px 8px; border-radius: 6px; font-family: monospace; }
    @media (max-width: 600px) { .container { padding: 12px 4vw; } }
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0; top: 0; width: 100vw; height: 100vh;
      background: rgba(0,0,0,0.7);
      justify-content: center;
      align-items: center;
    }
    .modal-content {
      background: #23272a;
      color: #fff;
      border-radius: 10px;
      max-width: 420px;
      width: 90vw;
      margin: auto;
      padding: 32px 28px 24px 28px;
      box-shadow: 0 2px 16px #000a;
      position: relative;
      font-size: 1.05rem;
      max-height: 90vh;
      overflow-y: auto;
    }
    .modal-close {
      position: absolute;
      top: 12px; right: 18px;
      font-size: 1.7rem;
      color: #8ab4f8;
      cursor: pointer;
      font-weight: bold;
      transition: color 0.2s;
    }
    .modal-close:hover {
      color: #ff6b6b;
    }
  </style>
</head>
<body>
<div class="header-wrap">
    <div class="header">
        <div class="header-left">
            <span class="logo"><a href="https://hapka.lol" class="logo-gradient" style="text-decoration:none;"><img src="logo.png" alt="logo" style="height:32px;vertical-align:middle;margin-right:10px;">hapka.lol</a></span>
        </div>
        <div class="header-right">
            <a href="/ShareX.php" class="header-link" style="margin-right:12px;">ShareX</a>
            <a href="https://github.com/Palitraq/hapka.lol" class="header-link">GitHub</a>
            <span class="nav-dot">&bull;</span>
            <a href="#" class="support-btn">&#10084; Support</a>
        </div>
    </div>
    <div class="header-underline"></div>
</div>
<div class="container upload-animation" style="margin-top:0;">
  <h1>ShareX Integration</h1>
  <p>You can upload files to <b>hapka.lol</b> automatically using <b>ShareX</b>.</p>
  <h2>ShareX Custom Uploader</h2>
  <ol>
    <li>Open <b>ShareX</b> &rarr; <b>Destinations</b> &rarr; <b>Custom uploader settings</b>.</li>
  </ol>
  <div style="margin: 24px 0 18px 0; text-align: center;">
    <div style="margin-bottom: 18px;">
      <img src="https://hapka.lol/uploads/img_685c407d2c6b32.02670156.png" alt="step1" style="max-width: 100%; border-radius: 10px; box-shadow: 0 2px 12px #0007;">
      <div style="color:#aaa; margin-top: 6px;">Step 1: How to open custom uploader settings in ShareX</div>
    </div>
    <div style="margin-bottom: 18px;">
      <img src="https://hapka.lol/PRCkYy" alt="step2" style="max-width: 100%; border-radius: 10px; box-shadow: 0 2px 12px #0007;">
      <div style="color:#aaa; margin-top: 6px;">Step 2: Fill in the custom uploader fields as shown</div>
      <div style="background:#181b20; color:#b6e1ff; border-radius:8px; margin:14px auto 0 auto; padding:12px 14px; max-width:480px; text-align:left; font-size:1em;">
        <div><b>Request URL:</b> <span style="user-select:all; cursor:pointer;" onclick="navigator.clipboard.writeText('https://hapka.lol/api/1/upload.php')">https://hapka.lol/api/1/upload.php</span></div>
        <div><b>Method:</b> <span style="user-select:all; cursor:pointer;" onclick="navigator.clipboard.writeText('POST')">POST</span></div>
        <div><b>File form name:</b> <span style="user-select:all; cursor:pointer;" onclick="navigator.clipboard.writeText('source')">source</span></div>
        <div><b>Parameters:</b> 
          <span style="user-select:all; cursor:pointer; margin-right:2px;" onclick="navigator.clipboard.writeText('key')">key</span>
          =
          <span style="user-select:all; cursor:pointer; margin-left:2px;" onclick="navigator.clipboard.writeText('dac5f11c-728d-402c-86ea-0d7d84d3e372')">dac5f11c-728d-402c-86ea-0d7d84d3e372</span>
        </div>
        <div><b>URL:</b> <span style="user-select:all; cursor:pointer;" onclick="navigator.clipboard.writeText('{json:success.url}')">{json:success.url}</span></div>
        <div><b>Body type:</b> <span style="user-select:all; cursor:pointer;" onclick="navigator.clipboard.writeText('Form data (multipart/form-data)')">Form data (multipart/form-data)</span></div>
      </div>
    </div>
    <div>
      <img src="https://hapka.lol/uploads/img_685c3eb0181432.02824010.png" alt="step3" style="max-width: 100%; border-radius: 10px; box-shadow: 0 2px 12px #0007;">
      <div style="color:#aaa; margin-top: 6px;">Step 3: Set this uploader as default if needed</div>
    </div>
  </div>
  <hr style="margin:32px 0 18px 0; border:0; border-top:1px solid #333;">
  <div style="font-size:0.98em;color:#aaa;">For questions or help, contact the site admin or open an issue on <a href="https://github.com/Palitraq/hapka.lol" target="_blank">GitHub</a>.</div>
</div>
<div id="support-modal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="modal-close" id="support-close">&times;</span>
    <h2>&hearts; Support my life</h2>
    <div style="margin-bottom: 12px;">
      <b>Bitcoin:</b><br>
      <span style="word-break:break-all;">bc1qh6r4ptmh5txv43c50s4wfv8ts5z06453ss3tmc</span>
    </div>
    <div style="margin-bottom: 12px;">
      <b>USDT TRC20:</b><br>
      <span style="word-break:break-all;">TNDvHcXWUcbjoJGQpEd6J3VygKT7RCHZ4g</span>
    </div>
    <div>
      <b>TON:</b><br>
      <span style="word-break:break-all;">UQCR0jBsHh8jSKw-hrs2cBehRg0rDdIOeZPOIiMYKoCBtQq9</span>
    </div>
    <div style="margin-top: 20px; display: flex; justify-content: center;">
      <div style="
        display: inline-block;
        background: #1a162b;
        color: #b388ff;
        font-size: 0.98em;
        font-weight: 500;
        border-radius: 7px;
        box-shadow: none;
        padding: 5px 14px;
        text-align: center;
        letter-spacing: 0.01em;
        line-height: 1.3;
        user-select: none;
        opacity: 0.7;
      ">
        <span style="font-size:1em; vertical-align:middle;">💜</span> Donates help pay for hosting.
      </div>
    </div>
<script>
// Support modal
if (document.querySelector('.support-btn')) {
    document.querySelector('.support-btn').onclick = function(e) {
        e.preventDefault();
        document.getElementById('support-modal').style.display = 'flex';
    };
    document.getElementById('support-close').onclick = function() {
        document.getElementById('support-modal').style.display = 'none';
    };
}
window.addEventListener('click', function(event) {
    let modal2 = document.getElementById('support-modal');
    if (modal2 && event.target === modal2) modal2.style.display = 'none';
});
</script>
</body>
</html> 