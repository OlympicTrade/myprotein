//VK Feedback
$(function() {
    $('<div id="vk_community_messages"></div>').appendTo('body');
    VK.Widgets.CommunityMessages("vk_community_messages", 122154011, {tooltipButtonText: "Есть вопрос?"});
});