<?php
/**
 Copyright 2022 Myers Enterprises II
 
 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at
 
 http://www.apache.org/licenses/LICENSE-2.0
 
 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
 */

$usage = "Usage: ksub.php jobname command <optional --cpu 750m> <optional --mem 2Gi> <optional --emails ...>\n";

if ($argc < 3) {
    echo $usage;
    exit;
}
   
$jobname = $argv[1];
$command = $argv[2];
$opts = [];

for ($x=3; $x < $argc; ++$x) {
    $opt = $argv[$x];
    
    switch ($opt) {
        case '--cpu':
            $opts['cpu'] = $argv[$x + 1];
            ++$x;
            break;
            
        case '--mem':
            $opts['mem'] = $argv[$x + 1];
            ++$x;
            break;
            
        case '--emails':
            $opts['emails'] = $argv[$x + 1];
            ++$x;
            break;
            
        default:
            echo "Invalid option $opt\n$usage";
            exit;
    }
}

$yaml = build_yaml($jobname, $command, $opts);

$error = send_yaml($yaml);

if (! empty($error)) {
    echo $error;
}

exit;

function send_yaml($yaml)
{
    $errmsg = '';
    $url = 'https://k8s.tools.eqiad1.wikimedia.cloud:6443/apis/batch/v1/namespaces/tool-bambots/jobs';
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'bambots (k8s api)');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSLCERT, '/data/project/bambots/.toolskube/client.crt');
    curl_setopt($ch, CURLOPT_SSLKEY, '/data/project/bambots/.toolskube/client.key');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($yaml));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $data = curl_exec($ch);
    $curl_err = curl_errno($ch);
    $resp_code = curl_getinfo($ch,  CURLINFO_RESPONSE_CODE);
    
    if ($data === false || $curl_err || $resp_code < 200 || $resp_code >= 300) {
        $errmsg .= "Response code: $resp_code\n";
        if ($data !== false) $errmsg .= "$data\n";
        if ($curl_err) $errmsg .= curl_strerror($curl_err) . "\n";
    }
    
    return $errmsg;
}

function build_yaml($jobname, $command, $opts = [])
{
    $labels = [
        'toolforge' => 'tool',
        'app.kubernetes.io/version' => '2',
        'app.kubernetes.io/managed-by' => 'toolforge-jobs-framework',
        'app.kubernetes.io/created-by' => 'bambots',
        'app.kubernetes.io/component' => 'jobs',
        'app.kubernetes.io/name' => $jobname,
        'jobs.toolforge.org/filelog' => 'yes',
        'jobs.toolforge.org/emails' => $opts['emails'] ?? 'none'
    ];
    
    $cpulimit = $opts['cpu'] ?? '500m';
    $memlimit = $opts['mem'] ?? '512Mi';
    
    $yaml = [
        'apiVersion' => 'batch/v1',
        'kind' => 'Job',
        'metadata' => [
            'name' => $jobname,
            'namespace' => 'tool-bambots',
            'labels' => $labels,
        ],
        'spec' => [
            'ttlSecondsAfterFinished' => 30,
            'backoffLimit' => 0,
            'template' => [
                'metadata' => ['labels' => $labels],
                'spec' => [
                    'restartPolicy' => 'Never',
                    'containers' => [
                        [
                            'name' => $jobname,
                            'image' => 'docker-registry.tools.wmflabs.org/toolforge-php82-sssd-base:latest',
                            'workingDir' => '/data/project/bambots',
                            'command' => ['/bin/sh', '-c', '--', "$command 1>>$jobname.out 2>>$jobname.err"],
                            'env' => [['name' => 'HOME', 'value' => '/data/project/bambots']],
                            'volumeMounts' =>  [['mountPath' => '/data/project', 'name' => 'home']],
                            'resources' => [
                                'limits' => ['cpu' => $cpulimit, 'memory' => $memlimit],
                                'requests' => ['cpu' => $cpulimit, 'memory' => $memlimit]
                            ]
                        ]
                    ],
                    'volumes' => [
                        [
                        'name' => 'home', 'hostPath' => ['path' => '/data/project', 'type' => 'Directory'],
                        ]
                    ],
                ],
            ]
        ]
    ];
    
    return $yaml;
}
