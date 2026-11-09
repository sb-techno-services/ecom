<?php
declare(strict_types=1);

final class HttpClient
{
    private string $cookie = '';

    public function __construct(private string $base) {}

    public function request(string $path, ?array $form = null, string $method = 'GET'): array
    {
        $headers = ['Connection: close'];
        if ($this->cookie !== '') {
            $headers[] = 'Cookie: ' . $this->cookie;
        }
        if ($form !== null) {
            $method = 'POST';
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        }
        $context = stream_context_create(['http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'content' => $form === null ? '' : http_build_query($form),
            'ignore_errors' => true,
            'follow_location' => 0,
            'timeout' => 6,
        ]]);
        $body = file_get_contents($this->base . $path, false, $context);
        if ($body === false) {
            throw new RuntimeException("HTTP request failed for $path");
        }
        $location = '';
        foreach ($http_response_header as $header) {
            if (preg_match('/^Set-Cookie: ([^;]+)/i', $header, $match)) {
                $this->cookie = $match[1];
            }
            if (preg_match('/^Location: (.+)/i', $header, $match)) {
                $location = $match[1];
            }
        }
        return [(int) explode(' ', $http_response_header[0])[1], $body, $location];
    }

    public static function token(string $body): string
    {
        if (!preg_match('/name="_csrf" value="([a-f0-9]{64})"/', $body, $match)) {
            throw new RuntimeException('CSRF token missing from form.');
        }
        return $match[1];
    }
}
