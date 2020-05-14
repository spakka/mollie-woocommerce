<?php # -*- coding: utf-8 -*-

namespace Mollie\WooCommerceTests\Unit\WC\Helper;

use function Brain\Monkey\Functions\expect;
use Mollie\WooCommerceTests\TestCase;
use Mollie_WC_Helper_PaymentMethodsIconUrl as Testee;

/**
 * Class PaymentMethodIconUrl
 */
class Payment_Method_Icon_Url_Test extends TestCase
{
    /**
     * Test PaymentMethodIconUrl returns svg url string when svgUrlForPaymentMethod is called
     *
     * @test
     */
    public function svgUrlForPaymentMethodReturnsSVGString()
    {
        /*
         * Setup stubs
         */
        $image = json_decode('{
                            "size1x": "https://mollie.com/external/icons/payment-methods/ideal.png",
                            "size2x": "https://mollie.com/external/icons/payment-methods/ideal%402x.png",
                            "svg": "https://mollie.com/external/icons/payment-methods/ideal.svg"
                            }');
        $paymentMethodsList = [
            "ideal" => $image
        ];

        /*
         * Setup Testee
         */
        $testee = new Testee ($paymentMethodsList);

        /*
         * Execute Test
         */
        $result = $testee->svgUrlForPaymentMethod('ideal');
        self::assertEquals('https://mollie.com/external/icons/payment-methods/ideal.svg',$result );
    }

    /**
     * Test PaymentMethodIconUrl returns svg url string when svgUrlForPaymentMethod is called
     *
     * @test
     */
    public function svgUrlForPaymentMethodFallBackToAssets()
    {
        /*
         * Setup stubs
         */
        $paymentMethod = 'ideal';
        $paymentMethodsList = [];

        /*
         * Setup Testee
         */
        $testee = new Testee($paymentMethodsList);

        /*
         * Execute Test
         */
        $result = $testee->svgUrlForPaymentMethod($paymentMethod);
        self::assertStringEndsWith('public/images/ideal.svg', $result);
    }

    /**
     * Test PaymentMethodIconUrl returns svg composed image when creditcard is provided
     *
     * @test
     */
    public function svgUrlForPaymentMethodCreditCardComposeSvg()
    {
        /*
         * Setup stubs
         */
        $paymentMethod = 'creditcard';
        $paymentMethodsList = [];
        $svgComposed = '/compositeCards.svg';

        /*
         * Setup Testee
         */
        $testee = $this->buildTesteeMethodMock(
            Testee::class,
            [$paymentMethodsList],
            ['enabledCreditcardOptions', 'enabledCreditcards', 'composeSvgImage']
        );

        /*
         * Expect to call is_admin() function and return false
         */
        expect('is_admin')
            ->once()
            ->withNoArgs()
            ->andReturn(false);
        $testee
            ->expects($this->once())
            ->method('enabledCreditcardOptions')
            ->willReturn(true);
        $testee
            ->expects($this->once())
            ->method('enabledCreditcards')
            ->willReturn(['cartasi.svg']);
        $testee
            ->expects($this->once())
            ->method('composeSvgImage')
            ->willReturn('/compositeCards.svg');


        /*
         * Execute Test
         */
        $result = $testee->svgUrlForPaymentMethod($paymentMethod);
        self::assertStringEndsWith($svgComposed, $result);
    }

    /**
     * Test PaymentMethodIconUrl returns svg composed image when creditcard is provided
     *
     * @test
     */
    public function svgUrlForPaymentMethodCreditCardFunctional()
    {
        /*
         * Setup stubs
         */
        $paymentMethod = 'creditcard';
        $paymentMethodsList = [];
        $svgComposed = '/compositeCards.svg';

        /*
         * Setup Testee
         */
        $testee = $this->buildTesteeMethodMock(
            Testee::class,
            [$paymentMethodsList],
            ['enabledCreditcardOptions', 'enabledCreditcards', 'writeToSvgFile']
        );

        /*
         * Expect to call is_admin() function and return false
         */
        expect('is_admin')
            ->once()
            ->withNoArgs()
            ->andReturn(false);

        $testee
            ->expects($this->once())
            ->method('enabledCreditcardOptions')
            ->willReturn(true);
        $testee
            ->expects($this->once())
            ->method('enabledCreditcards')
            ->willReturn(['cartasi.svg']);
        $testee
            ->expects($this->exactly(2))
            ->method('writeToSvgFile')
            ->willReturn('/compositeCards.svg');
        expect('file_get_contents')
            ->once()
            ->withAnyArgs()
            ->andReturn('<svg fill="none" height="24" viewBox="0 0 32 24" width="32" xmlns="http://www.w3.org/2000/svg"><g clip-rule="evenodd" fill-rule="evenodd"><path d="m4 0h24c2.2091 0 4 1.79086 4 4v16c0 2.2091-1.7909 4-4 4h-24c-2.20914 0-4-1.7909-4-4v-16c0-2.20914 1.79086-4 4-4z" fill="#ff6e28"/><path d="m5.39708.0876698c.21301-.0599205.43464-.0876698.65552-.0876698h.01549c.28676.00173033.55551.0506922.78753.134965.18744.068124.35081.159383.48049.267496.05398.044924.10418.095488.14826.150666.03225.04031.06116.083056.08579.128044.06688.122213.10158.260062.08389.406119-.01367.1124-.05662.21968-.12401.3187-.05127.07523-.11658.14579-.19393.21026-.0609.05076-.12917.0978-.20393.14048-.28424.16002-.65968.25737-1.07127.25737-.88697 0-1.60604-.45194-1.60604-1.00936 0-.29505.20142-.560495.5225-.745063.03011-.017111.06128-.033453.0934-.04909.10008-.048641.20948-.090041.32631-.1229172zm-5.176563 6.0324902c0-2.24853 3.925363-3.01198 5.920323-3.01198 1.12826 0 3.51906.24244 4.96736 1.50679.1867.16701.357.35151.5062.55524.0395.05384.0774.10901.1137.16554.0874.13592.1658.27973.2338.432.06.13439.0439.2318-.012.28474-.009.00852-.019.01589-.0299.02211-.0239.01358-.0521.02147-.0829.02339-.0907.00545-.1725-.03563-.2406-.09363-.0134-.01365-.0265-.02839-.0393-.04435-.0651-.08158-.1429-.16111-.232-.23834-.7405-.67034-2.32831-1.16771-3.78307-1.23269-1.58507-.07082-3.01212.37176-3.01212 1.66335v1.66618c0 .08055-.00246.1556-.00743.22532-.03301.46348-.17661.69534-.44592.78666-.12552.04262-.27832.05467-.45989.0455-.49315-.02512-1.74839-.2673-2.59632-.99987-.00195-.00166-.0039-.00333-.00579-.005-.462794-.41566-.794143-.98378-.794143-1.75096zm7.463763-.47097c.13182-.14605.3265-.20187.58964-.20187.0352 0 .07155.00096.10921.00288.77506.03948 3.43267.6151 3.43267 2.78782 0 2.24848-3.92544 3.01198-5.9204 3.01198-1.4171 0-4.82576-.3825-5.8572789-2.69162-.1575181-.35247.2092899-.4502.4408119-.16021 1.059677 1.32696 5.494877 2.12923 6.692037.65746.00523-.00603.01046-.01224.01562-.01833.03905-.04678.07483-.09587.10701-.14734.13056-.20879.20312-.45687.20312-.74814v-1.66617c0-.39952.06046-.6624.18756-.82646z" fill="#fff" transform="translate(10 6)"/></g></svg>');



        /*
         * Execute Test
         */
        $result = $testee->svgUrlForPaymentMethod($paymentMethod);
        self::assertStringEndsWith($svgComposed, $result);
    }
}
