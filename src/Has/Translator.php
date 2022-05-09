<?php


namespace App\Has;


use Symfony\Contracts\Translation\TranslatorInterface;


/**
 * A trait to use in your services, to quickly import trans().
 *
 * Trait Translator
 * @package App\Has
 */
trait Translator
{

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @return TranslatorInterface
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * @required → method will be magically called by the Dependency Injection Container
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    /**
     * Translates the given message.
     *
     * When a number is provided as a parameter named "%count%", the message is parsed for plural
     * forms and a translation is chosen according to this number using the following rules:
     *
     * Given a message with different plural translations separated by a
     * pipe (|), this method returns the correct portion of the message based
     * on the given number, locale and the pluralization rules in the message
     * itself.
     *
     * The message supports two different types of pluralization rules:
     *
     * interval: {0} There are no apples|{1} There is one apple|]1,Inf] There are %count% apples
     * indexed:  There is one apple|There are %count% apples
     *
     * The indexed solution can also contain labels (e.g. one: There is one apple).
     * This is purely for making the translations more clear - it does not
     * affect the functionality.
     *
     * The two methods can also be mixed:
     *     {0} There are no apples|one: There is one apple|more: There are %count% apples
     *
     * An interval can represent a finite set of numbers:
     *  {1,2,3,4}
     *
     * An interval can represent numbers between two numbers:
     *  [1, +Inf]
     *  ]-1,2[
     *
     * The left delimiter can be [ (inclusive) or ] (exclusive).
     * The right delimiter can be [ (exclusive) or ] (inclusive).
     * Beside numbers, you can use -Inf and +Inf for the infinite.
     *
     * @see https://en.wikipedia.org/wiki/ISO_31-11
     *
     * @param string      $id         The message id (may also be an object that can be cast to string)
     * @param array       $parameters An array of parameters for the message
     * @param string|null $domain     The domain for the message or null to use the default
     * @param string|null $locale     The locale or null to use the default
     *
     * @return string The translated string
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     */
    public function trans(string $id, $parameters = [], string $domain = null, string $locale = null)
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Useful to get an array out of a translation YAML file.
     * Will try to grab as much as it can, up to the end of the array.
     * If the passed key ($id) is not an array, an empty array is returned.
     *
     * No variable replacement in there for now.
     * We'll add the other params later when needed.
     *
     * @param string $id
     * @return string[]
     */
    public function transArray(string $id): array
    {
        $limit = 512; // move to signature after we've added the other params
        $array = [];

        $key_fmt = "${id}.%d";  // We can access array indices with this.
        $keep_at_it = true;  // We stop the loop as soon as the key is not found.
        $i = 0;
        do {
            $key = sprintf($key_fmt, $i);
            $value = $this->trans($key);
            if ($value == $key) {
                $keep_at_it = false; // or break?
            } else {
                $array[] = $value;
            }
        } while (++$i && $i < $limit && $keep_at_it);

        return $array;
    }
}