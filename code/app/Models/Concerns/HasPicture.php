<?php

namespace App\Models\Concerns;

trait HasPicture
{
    abstract public function getPictureUrlAttribute();

    public function defaultPictureText(): string
    {
        $tokens = explode(' ', $this->printableName());
        $tokens = array_values(array_filter($tokens, fn($t) => strlen($t) > 0 && preg_match('/^[a-zA-Z0-9]/', $t)));

        if (count($tokens) >= 2) {
            $text = mb_strtoupper(sprintf('%s%s', $tokens[0][0], $tokens[1][0]));
        }
        else {
            $text = mb_strtoupper(substr($tokens[0], 0, 2));
        }

        return $text;
    }

    public function hasPicture(): bool
    {
        return !empty($this->picture);
    }

    public function formatAvatar(): string
    {
        if ($this->hasPicture()) {
            return sprintf('<img class="avatar shadow" src="%s" loading="lazy">', $this->picture_url);
        }
        else {
            $name = $this->printableName();
            $color = colorFromText($name);
            return sprintf('<span class="avatar inline shadow" style="background-color: #%s">%s</span>', $color, $this->defaultPictureText());
        }
    }
}
