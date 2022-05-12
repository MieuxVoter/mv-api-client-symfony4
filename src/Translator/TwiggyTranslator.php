<?php

namespace App\Translator;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatorTrait;
use Twig\Environment as TwigEnvironment;


/**
 * This Service is meant to decorate the "translator" service.
 * It replaces the usual variable interpolation mechanism by Twig, for the configured domains.
 *
       App\Translator\TwiggyTranslator:
        decorates: translator
        arguments:
            # Translation domains to enable Twig for (other domains _should_ behave like usual)
            - ['messages']
            # Pass the old service as an argument, it has all the I18N config
            # This service id only exists because we're decorating the translator
            - '@App\Translator\TwiggyTranslator.inner'
            # Twig also has the extensions and global vars available
            - "@twig"
 *
 *
 * @method string getLocale()
 * @license WTFPL
 * @package App\Translator
 *
 */
class TwiggyTranslator implements TranslatorInterface, TranslatorBagInterface, LocaleAwareInterface
{

    use TranslatorTrait;

    ///
    ///

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var TwigEnvironment
     */
    private $twig;

    /**
     * @var array|string[]
     */
    private $domains;

    ///
    ///

    /**
     * Instantiate a TwiggyTranslator.  The Container will handle this for you.
     *
     * TwiggyTranslator constructor.
     * @param array $domains
     * @param Translator $translator
     * @param TwigEnvironment $twig
     */
    public function __construct(array $domains, Translator $translator, TwigEnvironment $twig)
    {
        $this->domains = $domains;
        $this->translator = $translator;
        $this->twig = $twig;
    }

    /**
     * @inheritDoc
     */
    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null)
    {
        if (null === $domain) {
            $domain = 'messages';
        }
        if (in_array($domain, $this->domains)) {
            $template = $this->translator->trans($id, [], $domain, $locale);
            $name = "${domain}__${id}";
            $tw = $this->twig->createTemplate($template, $name);

            // Move this to a plugin //
            // I. Grab all services extending a specific interface like TwiggyTranslatorPluginInterface
            // II. Run their hooks around here, perhaps one before template creation and one after
            $coin = 0;
            try {
                $coin = random_int(0, 1);
            } catch (\Exception $e) {} // there are three gods: true, false, random.
            if ( ! isset($parameters['e'])) {
                $parameters['e'] = (0 == $coin) ? 'e' : '';
            }
            ///////////////////////////

            return $this->twig->render($tw, $parameters);
        }

        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * @inheritDoc
     */
    public function getCatalogue($locale = null)
    {
        return $this->translator->getCatalogue($locale);
    }

}