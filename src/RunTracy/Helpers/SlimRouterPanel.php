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

class SlimRouterPanel implements IBarPanel
{
    private $routes;
    private $ver;
    private $icon;

    public function __construct($data = null, array $ver = [])
    {
        $this->routes = $data;
        $this->ver = $ver;
    }

    public function getTab(): string
    {
        $this->icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 480 480" width="16" '.
            'height="16"><path fill="#043CBF" d="m221.25 479.1c-79.88-6.85-150.37-51.83-189.99-121.24-14.666-'.
            '25.69-23.972-53.8-29.552-89.27-0.70825-4.5-1.1632-16.11-1.1384-29.06 0.04489-23.34 1.5027-35.94 6.5464'.
            '-56.54 14.241-58.18 49.919-109.05 99.764-142.23 30.47-20.29 60.66-31.962 98.61-38.123 10.115-1.6422 '.
            '16.564-2.0339 34.042-2.0675 12.945-0.024869 24.56 0.43011 29.062 1.1385 35.467 5.5797 63.58 14.886 '.
            '89.267 29.55 57.077 32.584 97.898 85.936 114.17 149.21 5.5391 21.544 7.2918 35.701 7.3122 59.062 0.0206'.
            ' 23.572-1.8064 38.623-7.2667 59.865-24.116 93.818-102.01 163.72-198.08 177.76-14.849 2.1694-39.536 '.
            '3.0819-52.743 1.9495zm147.17-86.443c6.6232-3.7977 20.436-17.61 24.234-24.234 3.1602-5.5114 3.8524-'.
            '11.08 1.9078-15.348-0.58243-1.2783-8.1762-8.2967-16.875-15.596-8.6988-7.2997-15.816-13.558-15.816-'.
            '13.908 0-0.34957 4.6702-5.269 10.378-10.932 11.155-11.067 13.059-13.734 13.059-18.291 0-4.5549-3.'.
            '8726-7.6619-13.125-10.53-54.096-16.771-102.54-28.47-110.07-26.58-1.3212 0.3316-3.1258 1.7073-4.0103'.
            ' 3.0572-1.5183 2.3172-1.5187 3.0709-0.007 13.499 2.4369 16.81 13.233 58.106 25.723 98.392 2.8685 '.
            '9.2524 5.9755 13.125 10.53 13.125 4.5563 0 7.2241-1.9048 18.291-13.059 5.6627-5.708 10.582-10.378 '.
            '10.932-10.378 0.34958 0 6.6121 7.122 13.917 15.827 7.3046 8.7046 14.198 16.27 15.318 16.812 4.1055 '.
            '1.9853 10.178 1.2641 15.618-1.8549zm-245.14-12.16c16.978-3.3216 54.928-13.606 84.554-22.913 14.55-4.57 '.
            '17.17-6.42 17.17-12.11 0-3.8998-3.1214-8.0561-14.43-19.214-4.9541-4.8883-9.0074-9.1866-9.0074-9.5519 '.
            '0-0.36526 6.4844-6.154 14.41-12.864 17.82-15.087 19.34-16.818 19.34-22.022 0-6.3122-3.9838-12.948-13.'.
            '584-22.626-13.104-13.21-21.795-17.121-29.353-13.206-1.3924 0.7212-8.3972 8.315-15.566 16.875-7.1691 '.
            '8.56-13.295 15.564-13.612 15.564-0.31764 0-4.577-4.0534-9.4652-9.0074-11.61-11.77-15.25-14.43-19.72-'.
            '14.43-6.7021 0-7.8427 2.5462-20.142 44.966-11.616 40.061-18.605 72.619-16.971 79.059 0.36534 1.4403 '.
            '1.6009 3.0154 2.7456 3.5004 2.6152 1.1078 11.526 0.34391 23.644-2.027zm165.44-147.65c1.6114-1.3559 '.
            '8.4196-8.9496 15.129-16.875 6.7098-7.9254 12.499-14.41 12.864-14.41 0.36526 0 4.6636 4.0534 9.5519 '.
            '9.0074 11.17 11.31 15.32 14.43 19.22 14.43 5.6821 0 7.5331-2.6238 12.11-17.165 9.29-29.517 19.583-'.
            '67.53 22.896-84.554 3.8458-19.764 3.5474-25.118-1.4709-26.391-6.4321-1.6316-39.009 5.3629-79.051 '.
            '16.973-42.41 12.3-44.96 13.44-44.96 20.15 0 4.4768 2.659 8.1117 14.43 19.726 4.9541 4.8883 9.0074 '.
            '9.1476 9.0074 9.4652 0 0.31763-7.0037 6.4432-15.564 13.612-8.56 7.1691-16.154 14.174-16.875 15.566-'.
            '3.9149 7.5582-0.005 16.25 13.206 29.353 13.33 13.222 22.786 16.784 29.519 11.119zm-67.538-12.074c4.'.
            '113-4.1288-1.1272-30.846-16.413-83.68-12.03-41.607-12.42-42.412-20.36-42.412-3.5784 0-4.4013 0.61734'.
            '-15.621 11.719-6.514 6.4453-12.106 11.719-12.427 11.719-0.3208 0-6.6212-7.1743-14.001-15.943-11.02-13.'.
            '095-14.119-16.211-17.342-17.442-6.7394-2.5738-13.567 0.69593-25.493 12.209-8.4352 8.143-12.884 14.075'.
            '-14.823 19.766-1.3474 3.9543-1.3446 4.675 0.03168 8.2788 1.2419 3.2519 4.3165 6.3115 17.45 17.365 '.
            '8.7686 7.3797 15.943 13.68 15.943 14.001 0 0.32082-5.2734 5.9129-11.719 12.427-11.101 11.22-11.719 '.
            '12.043-11.719 15.621 0 6.0237 2.4417 8.2094 12.935 11.58 28.29 9.0858 75.264 21.688 92.065 24.699 '.
            '14.99 2.6866 18.893 2.7025 21.498 0.0876z"/></svg>';
        return '<span title="Slim Router">'.$this->icon.'</span>';
    }

    public function getPanel(): string
    {
        $rows = [];
        foreach ($this->getData() as $row){
            $cells = array();
            foreach ($row as $cell) {
                $cells[] = '<td>'.$cell.'</td>';
            }
            $rows[] = "<tr>" . implode('', $cells) . "</tr>";
        }

        return '
        <style>
            #tracy-debug-panel-RunTracy-Helpers-SlimRouterPanel {max-width: 1440px !important}
        </style>
        <h1>'.$this->icon.' Slim '.$this->ver['slim'].' Router:</h1>
        <div class="tracy-inner">
                <table>
                    <tr class="yes">
                        <th><b>Id</b></th>
                        <th><b>Name</b></th>
                        <th><b>Pattern</b></th>
                        <th><b>Methods</b></th>
                        <th><b>Callable</b></th>
                        <th><b>Arguments</b></th>
                    </tr>'. implode('', $rows) .'
                </table>
         
        </div>';
    }

    private function getData(): array
    {
        $data = [];
        foreach ($this->routes as $route) {
            $callable = $route->getCallable();
            if(is_a($callable, 'Closure')){
                $callable = 'Closure';
            }elseif (is_array($callable)){
                $callable = implode(':', $callable);
            }

            $methods = $route->getMethods();
            if($this->isAny($methods)){
                $methods = ['(any)'];
            }

            $data[] = [
                'id'        => $route->getIdentifier(),
                'name'      => $route->getName() ?? '(unnamed)',
                'pattern'   => $route->getPattern(),
                'methods'   => implode(', ', $methods),
                'callable'  => $callable,
                'arguments' => json_encode($route->getArguments()),
            ];
        }

        return $data;
    }

    private function isAny($methods): bool
    {
        return empty(array_diff(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $methods));
    }
}
