// JavaScript
/**
 * Script PostFinance Checkout Flex (front - Next Gen)
 * - Pas de collecte de données sensibles côté front (redirection offsite).
 * - Affiche un bloc d’informations / logos.
 * - beforeCreatePayment ne renvoie pas de données particulières (le serveur crée la transaction).
 *
 * À placer (selon votre enqueueScript) sous:
 * plugins/postfinance-gateway-givewp/src/js/checkout-flex-gateway.js
 */
(() => {
    // Composant de champs pour l’UI (aucun champ requis)
    function PostFinanceGatewayFields() {
        const e = window.wp.element.createElement;

        const containerStyles = {
            padding: '12px',
            background: '#fafafa',
            border: '1px solid #eee',
            borderRadius: '6px',
            marginTop: '8px',
        };

        const logosStyle = { display: 'flex', gap: '8px', alignItems: 'center', marginTop: '8px' };
        const logoStyle = { height: '20px' };

        // Affiche un texte informatif + logos (adapter les URLs si besoin)
        return e(
            'div',
            { style: containerStyles, className: 'pf-checkout-flex-fields' },
            e('div', { style: { fontWeight: 600 } }, 'PostFinance Checkout Flex'),
            e(
                'p',
                null,
                "Vous serez redirigé vers la page de paiement sécurisée de PostFinance pour finaliser votre don."
            ),
            e(
                'div',
                { style: logosStyle },
                e('img', { src: '/wp-content/plugins/postfinance-gateway-givewp/assets/Mastercard-logo-s.png', style: logoStyle, alt: 'Mastercard' }),
                e('img', { src: '/wp-content/plugins/postfinance-gateway-givewp/assets/Visa_logo-s.png', style: logoStyle, alt: 'Visa' }),
                e('img', { src: '/wp-content/plugins/postfinance-gateway-givewp/assets/twint-logo-s.png', style: logoStyle, alt: 'TWINT' }),
                e('img', { src: '/wp-content/plugins/postfinance-gateway-givewp/assets/postfinance-logo-s.png', style: logoStyle, alt: 'PostFinance' }),
            )
        );
    }

    // Objet passerelle côté front
    const PostFinanceCheckoutFlexGateway = {
        id: 'postfinance-checkout-flex',

        // Les settings proviennent de formSettings côté PHP si nécessaires
        initialize() {
            // const { clientKey } = this.settings || {};
            // Rien d’obligatoire ici pour Flex (redirection offsite).
        },

        // Avant la création du paiement côté serveur
        async beforeCreatePayment() {
            // Pas de jeton ni de données sensibles à générer côté front.
            // Vous pouvez retourner un objet vide ou des métadonnées non sensibles si besoin.
            return {};
        },

        // Champs affichés sous l’option de passerelle
        Fields() {
            return window.wp.element.createElement(PostFinanceGatewayFields);
        },
    };

    // Enregistrement auprès de GiveWP
    window.givewp.gateways.register(PostFinanceCheckoutFlexGateway);
})();