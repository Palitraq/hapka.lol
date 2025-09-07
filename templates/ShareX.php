<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="/static/logo.png">
  <title>ShareX Integration — hapka.lol</title>
  <style>
    body { background: #181a1b; color: #e3e3e3; font-family: Arial, sans-serif; margin: 0; }

    /* Header */
    .header-wrap { max-width: 600px; margin: 40px auto 0 auto; }
    .header { display: flex; justify-content: space-between; align-items: center; padding: 18px 0 8px 0; background: transparent; max-width: 600px; margin: 0 auto; }
    .header-left { display: flex; align-items: center; gap: 24px; }
    .logo { font-size: 2rem; font-weight: 700; letter-spacing: 1px; display: flex; align-items: center; }
    .logo-gradient { background: linear-gradient(90deg, #8f5cff 0%, #5865f2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; text-fill-color: transparent; }
    .header-right { display: flex; align-items: center; gap: 8px; }
    .header-link { color: #8ab4f8; text-decoration: none; font-size: 1rem; padding: 4px 10px; border-radius: 6px; transition: background .2s, color .2s; }
    .header-link:hover { background: #23272a; color: #fff; }
    .nav-dot { color: #5865f2; margin: 0 4px; font-size: 1.1em; }
    .support-btn { background: #23272a; color: #fff; border-radius: 6px; padding: 4px 14px 4px 10px; font-size: 1rem; text-decoration: none; font-weight: 500; border: 1px solid #36393f; transition: background .2s, border .2s; display: inline-flex; align-items: center; gap: 4px; }
    .support-btn:hover { background: #5865f2; border: 1px solid #5865f2; color: #fff; }
    .header-underline { height: 3px; background: linear-gradient(90deg, #5865f2 0%, #8ab4f8 100%); border-radius: 2px; margin-bottom: 30px; max-width: 600px; margin-left: auto; margin-right: auto; }

    /* Page */
    .container { max-width: 600px; margin: 0 auto; background: #202225; border-radius: 12px; border: 1px solid #23272a; padding: 22px 28px 24px 28px; }
    .title { margin: 0 0 14px 0; color: #e3e3e3; }
    .download { display: inline-flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: 10px; font-weight: 600; color: #fff; text-decoration: none; background: linear-gradient(90deg, #7a4fd8 0%, #4a5bd8 100%); box-shadow: 0 8px 24px rgba(88,101,242,.35); border: 1px solid #4a5bd8; transition: transform .1s ease, box-shadow .2s ease, filter .2s ease; }
    .download:hover { transform: translateY(-1px); filter: brightness(1.05); box-shadow: 0 10px 28px rgba(88,101,242,.45); }
    .download:active { transform: translateY(0); }
    .note { color: #b9bbbe; margin-top: 8px; }
    .steps { margin-top: 16px; }
    .steps li { margin: 6px 0; }

    @media (max-width: 600px) {
      .container { padding: 16px 4vw; }
      .download { width: 100%; justify-content: center; }
    }

    /* Modal */
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.7); justify-content: center; align-items: center; }
    .modal-content { background: #23272a; color: #fff; border-radius: 10px; max-width: 420px; width: 90vw; margin: auto; padding: 32px 28px 24px 28px; box-shadow: 0 2px 16px #000a; position: relative; font-size: 1.05rem; max-height: 90vh; overflow-y: auto; }
    .modal-close { position: absolute; top: 12px; right: 18px; font-size: 1.7rem; color: #8ab4f8; cursor: pointer; font-weight: bold; transition: color .2s; }
    .modal-close:hover { color: #ff6b6b; }
  </style>
</head>
<body>
<div class="header-wrap">
    <div class="header">
        <div class="header-left">
        <span class="logo"><a href="https://hapka.lol" class="logo-gradient" style="text-decoration:none;"><img src="/static/logo.png" alt="logo" style="height:32px;vertical-align:middle;margin-right:10px;">hapka.lol</a></span>
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

  <div class="container">
    <h2 class="title">ShareX</h2>
    <a class="download" href="/static/hapka.lol.sxcu" download>Download ShareX config</a>

    <div class="steps">
      <ol>
        <li><b>Import</b> the file in ShareX:<br>Destinations → Custom uploader settings → Import.</li>
        <li><b>Select</b> "Custom image uploader" and "Custom file uploader" to make it default.</li>
  </ol>
    </div>

    <div class="note"></div>
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
        <div style="display: inline-block; background: #1a162b; color: #b388ff; font-size: 0.98em; font-weight: 500; border-radius: 7px; padding: 5px 14px; text-align: center; user-select: none; opacity: 0.7;">
        <span style="font-size:1em; vertical-align:middle;">💜</span> Donates help pay for hosting.
      </div>
    </div>
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
      var modal = document.getElementById('support-modal');
      if (modal && event.target === modal) modal.style.display = 'none';
});
</script>
</body>
</html> 