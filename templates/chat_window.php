<div class="header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-2 text-left">
        <a href="#messages" id="menu-icon-left"><i class="fa fa-arrow-left fa-2x"></i></a>
      </div>

      <div id="chatTitlePageName" class="col-8 text-center">
        <h1>Chat mit</h1>
      </div>
    </div>
  </div>
</div>

<div class="chat-container-wrapper">
  <div id="chat-text-container">
  </div>
</div>

<form onsubmit="submitChatInputField(); return false;">
  <div class="input-group chat-input">
    <input type="text" class="form-control" id="chat-input-field" placeholder="Textnachricht">
    <div class="input-group-append">
      <button class="btn btn-outline-secondary" id="chat-send-button" data-cid='+ 0 +' type="button">Senden</button>
    </div>
  </div>
</form>
