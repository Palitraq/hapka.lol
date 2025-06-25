<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ShareX Integration - hapka.lol</title>
  <style>
    body { background: #181b20; color: #e3e3e3; font-family: 'Segoe UI', Arial, sans-serif; margin: 0; }
    .container { max-width: 700px; margin: 40px auto 0 auto; background: #23272e; border-radius: 14px; box-shadow: 0 2px 16px #0005; padding: 32px 28px 28px 28px; }
    h1 { color: #8ab4f8; font-size: 2em; margin-top: 0; }
    h2 { color: #8ab4f8; margin-bottom: 8px; }
    pre { background: #181b20; padding: 12px 14px; border-radius: 8px; overflow-x: auto; font-size: 1em; color: #b6e1ff; }
    .warn { color: #ffb4b4; font-size: 1em; margin-top: 12px; }
    .api-key { color: #fff; background: #181b20; padding: 4px 8px; border-radius: 6px; font-family: monospace; }
    a { color: #8ab4f8; text-decoration: underline; }
    @media (max-width: 600px) { .container { padding: 12px 4vw; } }
  </style>
</head>
<body>
  <div class="container">
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
        <img src="https://hapka.lol/uploads/img_685c3c21ad4432.61384184.png" alt="step2" style="max-width: 100%; border-radius: 10px; box-shadow: 0 2px 12px #0007;">
        <div style="color:#aaa; margin-top: 6px;">Step 2: Fill in the custom uploader fields as shown</div>
        <div style="background:#181b20; color:#b6e1ff; border-radius:8px; margin:14px auto 0 auto; padding:12px 14px; max-width:480px; text-align:left; font-size:1em;">
          <div><b>Request URL:</b> <span style="user-select:all">https://hapka.lol/api/1/upload.php</span></div>
          <div><b>Method:</b> <span style="user-select:all">POST</span></div>
          <div><b>File form name:</b> <span style="user-select:all">source</span></div>
          <div><b>Parameters:</b> <span style="user-select:all">key = dac5f11c-728d-402c-86ea-0d7d84d3e372</span></div>
          <div><b>URL:</b> <span style="user-select:all">{json:success.image.url}</span></div>
          <div><b>Body type:</b> <span style="user-select:all">Form data (multipart/form-data)</span></div>
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
</body>
</html> 