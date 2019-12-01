//VK Feedback
/*$(function() {
    $('<div id="vk_community_messages"></div>').appendTo('body');
    VK.Widgets.CommunityMessages("vk_community_messages", 122154011, {tooltipButtonText: "Есть вопрос?"});
});*/
/*
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
})();*/
/*
document.write(unescape("%3Cscript src='" + "//yandex.mightycall.ru/c2c/js/MightyCallC2C_5.5.js' type='text/javascript'%3E%3C/script%3E"));
document.write(unescape("%3Cscript src='" + "//mightycallstorage.blob.core.windows.net/c2cjss/c2cb5c06-cda5-44a6-a64c-6b4f94908dc4.js' type='text/javascript'%3E%3C/script%3E"));
InitClick2Call();
*/
$.getScript('//yandex.mightycall.ru/c2c/js/MightyCallC2C_5.5.js');
$.getScript('//mightycallstorage.blob.core.windows.net/c2cjss/c2cb5c06-cda5-44a6-a64c-6b4f94908dc4.js', function() {
    InitClick2Call();
});