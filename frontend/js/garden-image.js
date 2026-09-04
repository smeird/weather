(function (window, document) {
  'use strict';

  var activeUrls = new WeakMap();

  function setStatus(state, text) {
    document.querySelectorAll('[data-garden-camera-status]').forEach(function (status) {
      status.dataset.state = state;
      var label = status.querySelector('[data-garden-camera-label]');
      if (label) label.textContent = text;
    });
  }

  function bytesToUrl(bytes) {
    if (!bytes || bytes.length < 2) return null;
    var data = bytes instanceof Uint8Array ? bytes : new Uint8Array(bytes);
    var mime = 'image/jpeg';
    if (data[0] === 0x89 && data[1] === 0x50) mime = 'image/png';
    if (data[0] === 0x52 && data[1] === 0x49 && data[2] === 0x46 && data[3] === 0x46) mime = 'image/webp';
    return URL.createObjectURL(new Blob([data], { type: mime }));
  }

  function base64ToUrl(value) {
    var clean = value.replace(/\s+/g, '');
    if (!clean) return null;
    try {
      var binary = window.atob(clean);
      var bytes = new Uint8Array(binary.length);
      for (var index = 0; index < binary.length; index++) bytes[index] = binary.charCodeAt(index);
      return bytesToUrl(bytes);
    } catch (error) {
      return null;
    }
  }

  function messageToUrl(message) {
    var bytes = message && message.payloadBytes;
    if (bytes && bytes.length > 3) {
      var raw = bytes instanceof Uint8Array ? bytes : new Uint8Array(bytes);
      var isJpeg = raw[0] === 0xff && raw[1] === 0xd8;
      var isPng = raw[0] === 0x89 && raw[1] === 0x50 && raw[2] === 0x4e && raw[3] === 0x47;
      var isWebp = raw[0] === 0x52 && raw[1] === 0x49 && raw[2] === 0x46 && raw[3] === 0x46;
      if (isJpeg || isPng || isWebp) return bytesToUrl(raw);
    }

    var payload = message && typeof message.payloadString === 'string' ? message.payloadString.trim() : '';
    if (!payload) return null;
    if (payload.indexOf('data:image/') === 0) return payload;

    if (payload.charAt(0) === '{') {
      try {
        var decoded = JSON.parse(payload);
        payload = decoded.image || decoded.data || decoded.payload || '';
        if (typeof payload !== 'string') return null;
        if (payload.indexOf('data:image/') === 0) return payload;
      } catch (error) {
        return null;
      }
    }

    return base64ToUrl(payload);
  }

  function renderMessage(message) {
    var imageUrl = messageToUrl(message);
    if (!imageUrl) {
      setStatus('error', 'Invalid image');
      return false;
    }

    document.querySelectorAll('[data-garden-image]').forEach(function (image) {
      var previousUrl = activeUrls.get(image);
      image.onload = function () {
        image.classList.remove('is-waiting');
        setStatus('live', 'Live camera');
        if (previousUrl && previousUrl.indexOf('blob:') === 0) URL.revokeObjectURL(previousUrl);
      };
      image.onerror = function () {
        image.classList.add('is-waiting');
        setStatus('error', 'Image unavailable');
        if (imageUrl.indexOf('blob:') === 0) URL.revokeObjectURL(imageUrl);
      };
      activeUrls.set(image, imageUrl);
      image.src = imageUrl;
    });
    return true;
  }

  function connect(options) {
    options = options || {};
    var topic = options.topic || 'weather/vegimage';
    var host = options.host || 'mqtt.smeird.com';
    var port = options.port || 8083;
    var attempts = 0;
    var client = new window.Paho.MQTT.Client(host, port, 'garden-' + Math.random().toString(16).slice(2));

    function connectNow() {
      client.connect({
        useSSL: true,
        onSuccess: function () {
          attempts = 0;
          client.subscribe(topic);
        },
        onFailure: reconnect
      });
    }

    function reconnect() {
      setStatus('waiting', 'Waiting for feed');
      var timeout = Math.min(30000, 1000 * Math.pow(2, attempts));
      attempts++;
      window.setTimeout(connectNow, timeout);
    }

    client.onConnectionLost = function () { reconnect(); };
    client.onMessageArrived = function (message) {
      if (message.destinationName === topic) renderMessage(message);
    };
    setStatus('waiting', 'Connecting');
    connectNow();
    return client;
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-garden-image]').forEach(function (image) {
      if (!image.complete || image.naturalWidth === 0) image.classList.add('is-waiting');
    });
  });

  window.SmeirdGardenImage = {
    connect: connect,
    renderMessage: renderMessage
  };
})(window, document);
