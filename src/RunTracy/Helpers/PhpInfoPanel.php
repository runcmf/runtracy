<?php
/**
 * Copyright 2016 1f7.wizard@gmail.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace RunTracy\Helpers;

use Tracy\IBarPanel;

class PhpInfoPanel implements IBarPanel
{
    private $icon;

    public function getTab()
    {
        $this->icon = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" x="0px" y="0px" viewBox="0 0 16.172 '.
            '16.172" style="enable-background:new 0 0 16.172 16.172;" xml:space="preserve" width="16px" '.
            'height="16px"><path d="M13.043,6.367c-0.237,0-0.398,0.022-0.483,0.047v1.523c0.101,0.022,0.223,0.03,'.
            '0.391,0.03 c0.621,0,1.004-0.313,1.004-0.842C13.954,6.651,13.625,6.367,13.043,6.367z" fill="#006DF0"/>'.
            ' <path d="M15.14,0H1.033C0.463,0,0,0.462,0,1.032v14.108c0,0.568,0.462,1.031,1.033,1.031H15.14 c0.57,'.
            '0,1.032-0.463,1.032-1.031V1.032C16.172,0.462,15.71,0,15.14,0z M4.904,8.32C4.506,8.695,3.916,8.863,'.
            '3.227,8.863    c-0.153,0-0.291-0.008-0.398-0.023v1.846H1.673V5.594c0.36-0.061,0.865-0.107,1.578-0.'.
            '107c0.719,0,1.233,0.139,1.577,0.414    C5.158,6.162,5.38,6.59,5.38,7.095S5.211,8.029,4.904,8.32z '.
            'M10.382,10.686H9.218v-2.16H7.297v2.16H6.125V5.526h1.172v1.983h1.921 V5.526h1.164C10.382,5.526,10.'.
            '382,10.686,10.382,10.686z M14.635,8.32c-0.397,0.375-0.987,0.543-1.677,0.543    c-0.152,0-0.291-0.'.
            '008-0.398-0.023v1.846h-1.155V5.594c0.359-0.061,0.864-0.107,1.577-0.107c0.72,0,1.232,0.139,1.577,'.
            '0.414    c0.33,0.261,0.552,0.689,0.552,1.194C15.11,7.6,14.942,8.029,14.635,8.32z" fill="#006DF0"/>'.
            '<path d="M3.312,6.367c-0.238,0-0.398,0.022-0.483,0.047v1.523c0.1,0.022,0.222,0.03,0.391,0.03 c0.62,'.
            '0,1.003-0.313,1.003-0.842C4.223,6.651,3.894,6.367,3.312,6.367z" fill="#006DF0"/></svg>';
        return '
        <span title="PHP Info">
            '.$this->icon.'
        </span>';
    }

    private function removeElementsByTagName($tagName, $document)
    {
        $nodeList = $document->getElementsByTagName($tagName);
        for ($nodeIdx = $nodeList->length; --$nodeIdx >= 0;) {
            $node = $nodeList->item($nodeIdx);
            $node->parentNode->removeChild($node);
        }
    }

    public function getPanel()
    {
        ob_start();
        phpinfo();
        $phpInfo = ob_get_contents();
        ob_get_clean();

        // warning:
        // DOMDocument::loadHTML(): htmlParseEntityRef: no name in Entity, line: 64
        // suppress warnings
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML($phpInfo);
        $body = $dom->getElementsByTagName('body')->item(0);
        $this->removeElementsByTagName('img', $body);
        $phpInfo = $dom->saveHTML($body);

        // http://php.net/manual/en/function.phpinfo.php#87287
        return '
        <h1>'.$this->icon.' PHP Info</h1>
        <div class="tracy-inner">
            <style type="text/css">
                .tracy-inner {max-width: 940px !important}
                #phpinfo pre {margin: 0; font-family: monospace;}
                #phpinfo a:link {color: #009; text-decoration: none; background-color: #fff;}
                #phpinfo a:hover {text-decoration: underline;}
                #phpinfo table {border-collapse: collapse; border: 0; width: 900px; box-shadow: 1px 2px 3px #ccc;}
                #phpinfo .center {text-align: center;}
                #phpinfo .center table {margin: 1em auto; text-align: left; width:100% !important;}
                #phpinfo .center th {text-align: center !important;}
                #phpinfo td, th {color: #111 !important; padding: 4px 5px;}
                #phpinfo h1 {font-size: 175%; max-width: 935px !important}
                #phpinfo h2 {font-size: 160%;}
                #phpinfo .p {text-align: left;}
                #phpinfo .e {background-color: #ccf !important; width: 300px; font-weight: bold; }
                #phpinfo .h th {background-color: #99c !important; font-weight: bold; }
                #phpinfo .v {background-color: #ddd !important; max-width: 300px; word-wrap: break-word;}
                #phpinfo .v i {color: #999 !important; }
                #phpinfo img {float: right; border: 0;}
                #phpinfo hr {background-color: #ccc; border: 0; height: 1px;}
            </style>
            <div id="phpinfo">' . $phpInfo . '</div>
        </div>';
    }
}
