chrome.runtime.onMessage.addListener(function(rq, sender, sendResponse) {
    // setTimeout to simulate any callback (even from storage.sync)
    setTimeout(function() {
        sendResponse({status: true});
    }, 1);
    // return true;  // uncomment this line to fix error
});