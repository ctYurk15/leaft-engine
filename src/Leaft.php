<?php

namespace ctyurk15\LeaftEngine;

class Leaft
{
    protected static $templates_path;
    protected $variables = [];

    public static function setTemplatePath($path){
        static::$templates_path = $path;
    }

    protected function parse($content)
    {
        $commands = [];
        preg_match_all('/{@(.*?)@}/s', $content, $commands);

        foreach ($commands[0] as $command)
        {
            //variables
            if(preg_match('/\$/i', $command))
            {
                preg_match('#{@.*\$(.+)@}#s', $command, $variable);
                $variable_name = preg_replace('/\'|"| /', '', $variable[1]);
                $variable_value = $this->variables[$variable_name];

                //arrays
                if(strpos($variable_name, '[') !== false)
                {
                    preg_match('/\[.*\]/i', $variable_name, $key);
                    $key = preg_replace('/\[|\]/', '', $key);
                    $variable_value = $this->variables[$variable_name][$key];
                    $short_variable_name = substr($variable_name, 0, strpos($variable_name, '['));
                    $variable_value = $this->variables[$short_variable_name][$key[0]];
                    //echo $short_variable_name;
                }

                $content = str_replace($command, $variable_value, $content);
            }

            //file includes
            if(preg_match('/include/i', $command))
            {
                preg_match('#{@.*include(.+)@}#s', $command, $template_name);
                $template_file_name = preg_replace('/\'|"| /', '', $template_name[1]);
                $template_file_content = $this->content($template_file_name);
                $content = str_replace($command, $template_file_content, $content);
            }
        }

        return $content;
    }

    public function content($template_name)
    {
        $template_path = static::$templates_path.'/'.$template_name.'.leaft.php';
        $template_content = file_get_contents($template_path);
        return $this->parse($template_content);
    }

    public function set($variable_name, $value)
    {
        $this->variables[$variable_name] = $value;
    }
}