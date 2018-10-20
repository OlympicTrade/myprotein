//Google
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', 'UA-121245947-1');

//Yandex
(function (d, w, c) {
    (w[c] = w[c] || []).push(function() {
        try {
            w.yaCounter47855381 = new Ya.Metrika2({
                id:47855381,
                clickmap:true,
                trackLinks:true,
                accurateTrackBounce:true,
                webvisor:true,
                ecommerce:"metrikaEc"
            });
        } catch(e) { }
    });

    var n = d.getElementsByTagName("script")[0],
        s = d.createElement("script"),
        f = function () { n.parentNode.insertBefore(s, n); };
    s.type = "text/javascript";
    s.async = true;
    s.src = "https://mc.yandex.ru/metrika/tag.js";

    if (w.opera == "[object Opera]") {
        d.addEventListener("DOMContentLoaded", f, false);
    } else { f(); }
})(document, window, "yandex_metrika_callbacks2");
window.metrikaEc = window.metrikaEc || [];

var getYandexCounter = function() {
    return yaCounter47855381;
};