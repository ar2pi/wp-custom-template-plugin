<?php
/*
 * Template Name: Light a Candle
 * Description: Custom "Light a Candle" landing page
 */

$nonce = wp_create_nonce('counter_nonce');
$ajaxUrl = admin_url('admin-ajax.php');

global $wpdb;
$table_name = $wpdb->prefix . PageTemplater::$table_suffix;
$counter = (double)$wpdb->get_results("SELECT `counter` FROM $table_name WHERE `id` = 1")[0]->counter;
$counter_str = number_format($counter);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?php echo the_title_attribute() ?></title>
    <meta name="description" content="Enciende una vela para el día de la paz">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,700">
    <style>
        html, body {
            font-family: 'Roboto', sans-serif;
            overflow: hidden;
        }
        .lac_container {
            height: 75vh;
            position: relative;
        }
        .lac_footer {
            height: 25vh;
            position: relative;
            text-align: center;
        }
        .lac_footer-img-container {
            display: inline-block;
            position: relative;
            height: 100%;
        }
        .lac_footer img {
            max-height: 100%;
            max-width: 90%;
        }
        .lac_center-block {
            position: absolute;
            top: 50%;
            left: 50%;
            margin-right: -50%;
            transform: translate(-50%, -50%)
        }
        .lac_cta-image {
            padding: 1em;
            cursor: pointer;
            text-align: center;
        }
        .lac_cta-image img {
            width: 10em;
            max-height: 38vh;
        }
        .lac_counter {
            text-align: center;
        }
        .lac_counter-number {
            padding: .2em;
            border-radius: 7px;
        }
        .lac_flash {
            -webkit-animation-name: flash-animation;
            -webkit-animation-duration: .9s;
            animation-name: flash-animation;
            animation-duration: .9s;
        }
        @-webkit-keyframes flash-animation {
            from { background: #FDF6BF; }
            to { background: inherit; }
        }
        @keyframes flash-animation {
            from { background: #FDF6BF; }
            to { background: inherit; }
        }
    </style>
</head>
<body>
<!--[if lte IE 9]>
<p>Está usando un navegador <strong>obsoleto</strong>. <a href="https://browsehappy.com/">Actualice su navegador</a>
    para mejorar su experiencia y seguridad.</p>
<![endif]-->
<div class="lac_container">
    <div class="lac_center-block">
        <div class="lac_cta-image js-ctaImage" data-state="off" data-nonce="<?php echo $nonce ?>">
            <img src="<?php echo plugin_dir_url(__FILE__) ?>assets/img/candle_off.svg" class="lac_off" alt="Vela apagada">
            <img src="<?php echo plugin_dir_url(__FILE__) ?>assets/img/candle_on.svg" class="lac_on" alt="Vela prendida" style="display: none;">
        </div>
        <p class="lac_counter">
            Numero de velas prendidas:
            <span class="lac_counter-number js-counterTarget"><?php echo $counter_str ?></span>
        </p>
    </div>
</div>
<div class="lac_footer">
    <div class="lac_footer-img-container">
        <img src="<?php echo plugin_dir_url(__FILE__) ?>assets/img/footer_banner.png" alt="Bogotá Ciudad de Paz, Juntos para la Paz, Organización de las Naciones Unidas, Alcaldía Mayor">
    </div>
</div>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script type="text/javascript">
    (function ($) {
        var lacVars = {
            nonce: '<?php echo $nonce ?>',
            ajaxUrl: '<?php echo $ajaxUrl ?>',
            counter: '<?php echo $counter ?>'
        };
        $(document).ready(main);
        function main() {
            $('.js-ctaImage').on('click', function () {
                if ($(this).data('state') === 'off') {
                    increment();
                    $(this).children('img.lac_off').hide();
                    $(this).children('img.lac_on').show();
                    $(this).data('state', 'on');
                }
            });
            refreshCounter();
        }
        function increment() {
            $.ajax({
                url: lacVars.ajaxUrl,
                method: 'POST',
                dataType: 'json',
                data: {'action': 'increment_counter', 'nonce': lacVars.nonce},
                success: updateCounter,
                error: function (xhr, str, error) {
                    alert('Wups occurrio un pequeño error, por favor vuelve a intentar nuevamente mas tarde :)')
                }
            });
            updateCounter({counter: (+lacVars.counter) + 1});
        }
        function refreshCounter() {
            $.ajax({
                url: lacVars.ajaxUrl,
                method: 'POST',
                dataType: 'json',
                data: {'action': 'refresh_counter', 'nonce': lacVars.nonce},
                success: updateCounter,
                error: function (xhr, str, error) {
                    console.log('error loading data', arguments);
                },
                complete: function (xhr, status) {
                    setTimeout(refreshCounter, 3000);
                }
            });
        }
        function updateCounter(data, status, xhr) {
            var $counter = $('.js-counterTarget');
            if (counterShouldUpdate(data, $counter)) {
                $counter.text(numberFormat(data.counter))
                    .addClass('lac_flash');
                setTimeout(function () {
                    $counter.removeClass('lac_flash');
                }, 500);
                lacVars.counter = data.counter;
            }
        }
        function counterShouldUpdate(data, target) {
            return !!data && !!data.counter && +data.counter !== +target.text().replace(',', '');
        }
        function numberFormat(int) {
            var bacon = int.toString().split('').reverse();
            for (var i = 0; i < bacon.length; i++) {
                if ((i + 1) % 4 === 0) {
                    bacon.splice(i, 0, ',');
                }
            }
            return bacon.reverse().join('');
        }
    })(jQuery);
</script>
</body>
</html>
