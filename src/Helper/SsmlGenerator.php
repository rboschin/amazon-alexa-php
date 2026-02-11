<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Helper;

use Rboschin\AmazonAlexa\Exception\InvalidSsmlException;

class SsmlGenerator implements SsmlTypes
{
    /**
     * @param bool $escapeSpecialChars Enable this flag when you need escaped special chars in your content
     * @param string[] $parts Array of SSML parts
     */
    public function __construct(
        public bool $escapeSpecialChars = false,
        private array $parts = [],
    ) {
    }

    /**
     * Create a new SsmlGenerator instance
     */
    public static function create(bool $escapeSpecialChars = false): self
    {
        return new self($escapeSpecialChars);
    }

    /**
     * Clear current ssml parts.
     */
    public function clear(): self
    {
        $this->parts = [];
        return $this;
    }

    public function getSsml(): string
    {
        return sprintf('<speak>%s</speak>', implode(' ', $this->parts));
    }

    /**
     * Say a default text.
     */
    public function say(string $text): self
    {
        $this->parts[] = $this->textEscapeSpecialChars($text);
        return $this;
    }

    /**
     * Play audio in output.
     * For more specifications of the mp3 file @see https://developer.amazon.com/de/docs/custom-skills/speech-synthesis-markup-language-ssml-reference.html#audio.
     *
     * @throws InvalidSsmlException
     */
    public function playMp3(string $mp3Url): self
    {
        if (1 !== preg_match('/^(https:\/\/.*\.mp3.*)$/i', $mp3Url) && 0 !== strpos($mp3Url, 'soundbank://')) {
            throw new InvalidSsmlException(sprintf('"%s" in not a valid mp3 url!', $mp3Url));
        }
        $this->parts[] = sprintf('<audio src="%s" />', $mp3Url);
        return $this;
    }

    /**
     * Make a pause (or remove with none/x-weak).
     * Possible values @see https://developer.amazon.com/de/docs/custom-skills/speech-synthesis-markup-language-ssml-reference.html#break.
     *
     * @throws InvalidSsmlException
     */
    public function pauseStrength(string $strength): self
    {
        if (!in_array($strength, self::BREAK_STRENGTHS, true)) {
            throw new InvalidSsmlException(sprintf('Break strength must be one of "%s"!', implode(',', self::BREAK_STRENGTHS)));
        }
        $this->parts[] = sprintf('<break strength="%s" />', $strength);
        return $this;
    }

    /**
     * Make a pause with duration time as string in seconds(s) or milliseconds(ms).
     * For example '10s' or '10000ms' to break 10 seconds.
     *
     * @throws InvalidSsmlException
     */
    public function pauseTime(string $time): self
    {
        if (1 !== preg_match('/^(\d+(s|ms))$/i', $time)) {
            throw new InvalidSsmlException('Time must be seconds or milliseconds!');
        }
        $this->parts[] = sprintf('<break time="%s" />', $time);
        return $this;
    }

    /**
     * Say a text with effect.
     *
     * @throws InvalidSsmlException
     */
    public function sayWithAmazonEffect(string $text, string $effect = self::AMAZON_EFFECT_WHISPERED): self
    {
        if (!in_array($effect, self::AMAZON_EFFECTS, true)) {
            throw new InvalidSsmlException(sprintf('Amazon:effect name must be one of "%s"!', implode(',', self::AMAZON_EFFECTS)));
        }
        $this->parts[] = sprintf('<amazon:effect name="%s">%s</amazon:effect>', $effect, $this->textEscapeSpecialChars($text));
        return $this;
    }

    /**
     * Whisper a text.
     */
    public function whisper(string $text): self
    {
        return $this->sayWithAmazonEffect($text, self::AMAZON_EFFECT_WHISPERED);
    }

    /**
     * Say with emphasis.
     *
     * @throws InvalidSsmlException
     */
    public function emphasis(string $text, string $level): self
    {
        if (!in_array($level, self::EMPHASIS_LEVELS, true)) {
            throw new InvalidSsmlException(sprintf('Emphasis level must be one of "%s"!', implode(',', self::EMPHASIS_LEVELS)));
        }
        $this->parts[] = sprintf('<emphasis level="%s">%s</emphasis>', $level, $this->textEscapeSpecialChars($text));
        return $this;
    }

    /**
     * Say a text pronounced in the given language.
     *
     * @throws InvalidSsmlException
     */
    public function pronounceInLanguage(string $language, string $text): void
    {
        if (!in_array($language, self::LANGUAGE_LIST, true)) {
            throw new InvalidSsmlException(sprintf('Language must be one of "%s"!', implode(',', self::LANGUAGE_LIST)));
        }
        $this->parts[] = sprintf('<lang xml:lang="%s">%s</lang>', $language, $this->textEscapeSpecialChars($text));
    }

    /**
     * Say a paragraph.
     */
    public function paragraph(string $paragraph): void
    {
        $this->parts[] = sprintf('<p>%s</p>', $this->textEscapeSpecialChars($paragraph));
    }

    /**
     * Say a text with a phoneme.
     *
     * @throws InvalidSsmlException
     */
    public function phoneme(string $alphabet, string $ph, string $text): void
    {
        if (!in_array($alphabet, self::PHONEME_ALPHABETS, true)) {
            throw new InvalidSsmlException(sprintf('Phoneme alphabet must be one of "%s"!', implode(',', self::PHONEME_ALPHABETS)));
        }
        $this->parts[] = sprintf('<phoneme alphabet="%s" ph="%s">%s</phoneme>', $alphabet, $ph, $this->textEscapeSpecialChars($text));
    }

    /**
     * Say a text with a prosody.
     *
     * There are three different modes of prosody: volume, pitch, and rate.
     * For more details @see https://developer.amazon.com/de/docs/custom-skills/speech-synthesis-markup-language-ssml-reference.html#prosody
     *
     * @throws InvalidSsmlException
     */
    public function prosody(string $mode, string $value, string $text): void
    {
        if (!isset(self::PROSODIES[$mode])) {
            throw new InvalidSsmlException(sprintf('Prosody mode must be one of "%s"!', implode(',', array_keys(self::PROSODIES))));
        }
        // todo validate value for mode
        $this->parts[] = sprintf('<prosody %s="%s">%s</prosody>', $mode, $value, $this->textEscapeSpecialChars($text));
    }

    /**
     * Say a sentence.
     */
    public function sentence(string $text): void
    {
        $this->parts[] = sprintf('<s>%s</s>', $this->textEscapeSpecialChars($text));
    }

    /**
     * Say a text with interpretation.
     *
     * @throws InvalidSsmlException
     */
    public function sayAs(string $interpretAs, string $text, string $format = ''): void
    {
        if (!in_array($interpretAs, self::SAY_AS_INTERPRET_AS, true)) {
            throw new InvalidSsmlException(sprintf('Interpret as attribute must be one of "%s"!', implode(',', self::SAY_AS_INTERPRET_AS)));
        }
        if ($format) {
            $this->parts[] = sprintf('<say-as interpret-as="%s" format="%s">%s</say-as>', $interpretAs, $format, $this->textEscapeSpecialChars($text));
        } else {
            $this->parts[] = sprintf('<say-as interpret-as="%s">%s</say-as>', $interpretAs, $this->textEscapeSpecialChars($text));
        }
    }

    /**
     * Say an alias.
     * For example replace the abbreviated chemical elements with the full words.
     */
    public function alias(string $alias, string $text): void
    {
        $this->parts[] = sprintf('<sub alias="%s">%s</sub>', $alias, $this->textEscapeSpecialChars($text));
    }

    /**
     * Say a text with the voice of the given person.
     *
     * @throws InvalidSsmlException
     */
    public function sayWithVoice(string $voice, string $text): void
    {
        if (!in_array($voice, self::VOICES, true)) {
            throw new InvalidSsmlException(sprintf('Voice must be one of "%s"!', implode(',', self::VOICES)));
        }
        $this->parts[] = sprintf('<voice name="%s">%s</voice>', $voice, $this->textEscapeSpecialChars($text));
    }

    /**
     * Say a word with defined word's parts to speach.
     *
     * @throws InvalidSsmlException
     */
    public function word(string $role, string $text): void
    {
        if (!in_array($role, self::INTERPRET_WORDS, true)) {
            throw new InvalidSsmlException(sprintf('Interpret as attribute must be one of "%s"!', implode(',', self::INTERPRET_WORDS)));
        }
        $this->parts[] = sprintf('<w role="%s">%s</w>', $role, $this->textEscapeSpecialChars($text));
    }

    /**
     * Escape special chars for ssml output (for example "&").
     */
    private function textEscapeSpecialChars(string $text): string
    {
        if ($this->escapeSpecialChars) {
            $text = htmlspecialchars($text);
        }

        return $text;
    }

    /**
     * Say a number with proper pronunciation
     */
    public function number(int $number): self
    {
        $this->parts[] = sprintf('<say-as interpret-as="number">%d</say-as>', $number);
        return $this;
    }

    /**
     * Say an ordinal number (1st, 2nd, 3rd, etc.)
     */
    public function ordinal(int $number): self
    {
        $this->parts[] = sprintf('<say-as interpret-as="ordinal">%d</say-as>', $number);
        return $this;
    }

    /**
     * Say digits individually
     */
    public function digits(string $digits): self
    {
        $this->parts[] = sprintf('<say-as interpret-as="digits">%s</say-as>', $digits);
        return $this;
    }

    /**
     * Create a list of items with automatic pauses
     */
    public function list(array $items, string $pause = 'medium'): self
    {
        foreach ($items as $index => $item) {
            $this->say($item);
            
            // Add pause between items except after the last one
            if ($index < count($items) - 1) {
                $this->pauseStrength($pause);
            }
        }
        
        return $this;
    }

    /**
     * Create a countdown with pauses
     */
    public function countdown(int $start, int $end = 1): self
    {
        for ($i = $start; $i >= $end; $i--) {
            $this->number($i);
            
            if ($i > $end) {
                $this->pauseTime('1s');
            }
        }
        
        return $this;
    }

    /**
     * Spell out a word letter by letter
     */
    public function spell(string $word): self
    {
        $letters = str_split(strtoupper($word));
        
        foreach ($letters as $index => $letter) {
            $this->say($letter);
            
            // Add small pause between letters except after the last one
            if ($index < count($letters) - 1) {
                $this->pauseTime('200ms');
            }
        }
        
        return $this;
    }
}
