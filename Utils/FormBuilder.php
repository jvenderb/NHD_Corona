<?php
declare(strict_types=1);
namespace Corona\Utils;

/**
 * Class FormBuilder
 * @package Corona\Utils
 * Responsibility: Returns the form to process the Corona numbers.
 */
class FormBuilder
{
    const TEMPLATE_FILE = './Resources/FormTemplate.html';
    private string $form;

    public function __construct()
    {
        $this->form = '';
    }

    public function getInPutForm(string $messages): string
    {
        $this->form = file_get_contents(self::TEMPLATE_FILE);
        $this->form = str_replace('<!--MESSAGES-->', $messages, $this->form);
        return $this->form;
    }

}

