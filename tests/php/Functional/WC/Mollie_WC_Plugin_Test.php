<?php # -*- coding: utf-8 -*-

namespace Mollie\WooCommerceTests\Functional\WC;

use Mollie\WooCommerceTests\TestCase;
use Mollie_WC_Plugin;
use WpScriptsStub;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\when;

/**
 * Class Mollie_WC_Plugin_Test
 */
class Mollie_WC_Plugin_Test extends TestCase
{
    public function testGetPluginUrl()
    {
        /*
         * Stubs
         */
        $path = uniqid();
        when('untrailingslashit')->returnArg(1);

        /*
         * Execute test
         */
        self::assertEquals(
            M4W_PLUGIN_URL . '/',
            Mollie_WC_Plugin::getPluginPath()
        );
        self::assertEquals(
            M4W_PLUGIN_URL . "/{$path}",
            Mollie_WC_Plugin::getPluginPath("/{$path}")
        );
        self::assertEquals(
            M4W_PLUGIN_URL . '/',
            Mollie_WC_Plugin::getPluginPath('/')
        );
    }

    public function testGetPluginDir()
    {
        /*
         * Stubs
         */
        $path = uniqid();
        when('untrailingslashit')->returnArg(1);

        /*
         * Execute test
         */
        self::assertEquals(
            M4W_PLUGIN_DIR . '/',
            Mollie_WC_Plugin::getPluginPath()
        );
        self::assertEquals(
            M4W_PLUGIN_DIR . "/{$path}",
            Mollie_WC_Plugin::getPluginPath("/{$path}")
        );
        self::assertEquals(
            M4W_PLUGIN_DIR . '/',
            Mollie_WC_Plugin::getPluginPath('/')
        );
    }

    public function testRegisterFrontendScripts()
    {
        /*
         * Execute Test
         */
        Mollie_WC_Plugin::registerFrontendScripts();

        $wpScriptsStub = WpScriptsStub::instance();

        /*
         * Polyfill
         */
        $babelPolifyll = $wpScriptsStub->registered('script', 'babel-polyfill');
        self::assertEquals('babel-polyfill', $babelPolifyll[0]);
        self::assertEquals(M4W_PLUGIN_URL . '/assets/js/babel-polyfill.min.js', $babelPolifyll[1]);
        self::assertEquals(
            filemtime(M4W_PLUGIN_DIR . '/assets/js/babel-polyfill.min.js'),
            $babelPolifyll[3]
        );
        self::assertEquals(true, $babelPolifyll[4]);

        /*
         * Apple Pay
         */
        $applepayScript = $wpScriptsStub->registered('script', 'mollie_wc_gateway_applepay');
        self::assertEquals('mollie_wc_gateway_applepay', $applepayScript[0]);
        self::assertEquals(M4W_PLUGIN_URL . '/assets/js/applepay.min.js', $applepayScript[1]);
        self::assertEquals([], $applepayScript[2]);
        self::assertEquals(
            filemtime(M4W_PLUGIN_DIR . '/assets/js/applepay.min.js'),
            $applepayScript[3]
        );
        self::assertEquals(true, $applepayScript[4]);

        /*
         * Mollie Components Css
         */
        $mollieComponentsStyle = $wpScriptsStub->registered('style', 'mollie-components');
        self::assertEquals('mollie-components', $mollieComponentsStyle[0]);
        self::assertEquals(
            M4W_PLUGIN_URL . '/assets/css/mollie-components.css',
            $mollieComponentsStyle[1]
        );
        self::assertEquals([], $mollieComponentsStyle[2]);
        self::assertEquals(
            filemtime(M4W_PLUGIN_DIR . '/assets/css/mollie-components.css'),
            $mollieComponentsStyle[3]
        );
        self::assertEquals('screen', $mollieComponentsStyle[4]);

        /*
         * Mollie JS
         */
        $mollieScript = $wpScriptsStub->registered('script', 'mollie');
        self::assertEquals('mollie', $mollieScript[0]);
        self::assertEquals('https://js.mollie.com/v1/mollie.js', $mollieScript[1]);
        self::assertEquals([], $mollieScript[2]);
        self::assertEquals(null, $mollieScript[3]);
        self::assertEquals(true, $mollieScript[4]);

        /*
         * Mollie Components Js
         */
        $mollieComponentsScript = $wpScriptsStub->registered('script', 'mollie-components');
        self::assertEquals('mollie-components', $mollieComponentsScript[0]);
        self::assertEquals(
            M4W_PLUGIN_URL . '/assets/js/mollie-components.min.js',
            $mollieComponentsScript[1]
        );
        self::assertEquals(['jquery', 'mollie', 'babel-polyfill'], $mollieComponentsScript[2]);
        self::assertEquals(
            filemtime(M4W_PLUGIN_DIR . '/assets/js/mollie-components.min.js'),
            $mollieComponentsScript[3]
        );
        self::assertEquals(true, $mollieComponentsScript[4]);
    }

    public function testEnqueueFrontendScriptsInCheckoutPage()
    {
        /*
         * Stubs
         */
        stubs(
            [
                'is_admin' => false,
                'isCheckoutContext' => true,
            ]
        );

        /*
         * Execute Test
         */
        Mollie_WC_Plugin::enqueueFrontendScripts();

        $wpScriptsStub = WpScriptsStub::instance();

        self::assertEquals(
            true,
            $wpScriptsStub->isEnqueued('script', 'mollie_wc_gateway_applepay')
        );
    }

    /**
     * @dataProvider frontendCheckoutContextDataProvider
     * @param $isAdmin
     * @param $isCheckoutContext
     */
    public function testEnqueueFrontendScriptsDoesNotEnqueueInInvalidContext(
        $isAdmin,
        $isCheckoutContext
    ) {

        /*
         * Stubs
         */
        stubs(
            [
                'is_admin' => $isAdmin,
                'isCheckoutContext' => $isCheckoutContext,
            ]
        );

        /*
         * Execute Test
         */
        Mollie_WC_Plugin::enqueueFrontendScripts();

        self::assertEquals(true, empty(WpScriptsStub::instance()->allEnqueued('script')));
    }

    public function testEnqueueComponentsAssets()
    {
        stubs(
            [
                'merchantProfileId' => uniqid(),
                'mollieComponentsStylesForAvailableGateways' => [uniqid()],
                'is_admin' => false,
                'isCheckoutContext' => true,
                'get_locale' => uniqid(),
                'isTestModeEnabled' => true,
                'esc_html__' => '',
                'is_checkout' => true,
                'is_checkout_pay_page' => false
            ]
        );

        /*
         * Expect to localize script for mollie components
         */
        expect('wp_localize_script')
            ->once()
            ->andReturnUsing(
                function ($handle, $objectName, $data) {
                    self::assertEquals('mollie-components', $handle);
                    self::assertEquals('mollieComponentsSettings', $objectName);
                    self::assertEquals('array', gettype($data));
                }
            );

        /*
         * Execute Test
         */
        Mollie_WC_Plugin::enqueueComponentsAssets();

        $wpScriptsStub = WpScriptsStub::instance();

        self::assertEquals(true, $wpScriptsStub->isEnqueued('style', 'mollie-components'));
        self::assertEquals(true, $wpScriptsStub->isEnqueued('script', 'mollie-components'));
    }

    public function frontendCheckoutContextDataProvider()
    {
        // Admin, CheckoutPage
        return [
            [false, false],
            [true, false],
        ];
    }
}
