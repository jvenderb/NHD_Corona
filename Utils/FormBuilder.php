<?php
declare(strict_types=1);

namespace Corona\Utils;
/**
 * Returns the form to process the Corona numbers.
 */
class FormBuilder
{
    const TEMPLATE_FILE = '/Users/jvenderb/phpStormProjects/NHD_Corona/Resources/FormTemplate.html';
    /** @var $form string */
    private string $form;


    public function __construct()
    {
        $this->form = '';
    }

    public function getInPutForm($messages): string
    {
        $this->form = file_get_contents(self::TEMPLATE_FILE);
        $this->form = str_replace('<!--MESSAGES-->', $messages, $this->form);
        return $this->form;
    }

}

