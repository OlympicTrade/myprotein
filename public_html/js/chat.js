//VK Feedback
/*$(function() {
    $('<div id="vk_community_messages"></div>').appendTo('body');
    VK.Widgets.CommunityMessages("vk_community_messages", 122154011, {tooltipButtonText: "Есть вопрос?"});
});*/

(function () {
    window['yandexChatWidgetCallback'] = function() {
        try {
            window.yandexChatWidget = new Ya.ChatWidget({
                guid: '58109b48-90db-4e36-b9ff-4579833b2a47',
                buttonText: '',
                title: 'Чат',
                theme: 'light',
                collapsedDesktop: 'never',
                collapsedTouch: 'always'
            });
        } catch(e) { }
    };
    var n = document.getElementsByTagName('script')[0],
        s = document.createElement('script');
    s.async = true;
    s.charset = 'UTF-8';
    s.src = 'https://chat.s3.yandex.net/widget.js';
    n.parentNode.insertBefore(s, n);
})();