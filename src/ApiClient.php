<?php

declare(strict_types=1);

namespace Elonn\Site;

final class ApiClient
{
    public function __construct(private string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * @return array{ok: bool, status: int, token?: string, expires_at?: string, error?: string}
     */
    public function register(string $email, string $password, ?string $displayName): array
    {
        return $this->jsonPost('/identity/register', [
            'email' => $email,
            'password' => $password,
            'display_name' => $displayName,
        ]);
    }

    /**
     * @return array{ok: bool, status: int, token?: string, expires_at?: string, error?: string}
     */
    public function login(string $email, string $password): array
    {
        return $this->jsonPost('/identity/login', [
            'email' => $email,
            'password' => $password,
        ]);
    }

    /**
     * @return array{id: string, email: string, display_name: string|null}|null
     */
    public function me(string $token): ?array
    {
        $response = $this->request('GET', '/identity/me', null, $token);
        if ($response['status'] !== 200 || !is_array($response['json'])) {
            return null;
        }

        $id = $response['json']['id'] ?? null;
        $email = $response['json']['email'] ?? null;
        if ((!is_string($id) && !is_int($id)) || !is_string($email)) {
            return null;
        }

        $displayName = $response['json']['display_name'] ?? null;
        return [
            'id' => (string) $id,
            'email' => $email,
            'display_name' => is_string($displayName) ? $displayName : null,
        ];
    }

    public function logout(string $token): void
    {
        $this->request('POST', '/identity/logout', [], $token);
    }

    /**
     * @param array<string, mixed> $body
     * @return array{ok: bool, status: int, token?: string, expires_at?: string, error?: string}
     */
    private function jsonPost(string $path, array $body): array
    {
        $response = $this->request('POST', $path, $body, null);
        $json = is_array($response['json']) ? $response['json'] : [];

        return [
            'ok' => $response['status'] >= 200 && $response['status'] < 300,
            'status' => $response['status'],
            'token' => is_string($json['token'] ?? null) ? $json['token'] : null,
            'expires_at' => is_string($json['expires_at'] ?? null) ? $json['expires_at'] : null,
            'error' => is_string($json['error'] ?? null) ? $json['error'] : null,
        ];
    }

    /**
     * @param array<string, mixed>|null $body
     * @return array{status: int, json: mixed}
     */
    private function request(string $method, string $path, ?array $body, ?string $token): array
    {
        $headers = ['Accept: application/json'];
        $content = '';

        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
            $content = json_encode($body, JSON_UNESCAPED_SLASHES) ?: '{}';
        }

        if ($token !== null) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => $content,
                'ignore_errors' => true,
                'timeout' => 5,
            ],
        ]);

        $raw = @file_get_contents($this->baseUrl . $path, false, $context);
        $status = 0;
        foreach ($http_response_header ?? [] as $header) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $matches) === 1) {
                $status = (int) $matches[1];
                break;
            }
        }

        return [
            'status' => $status,
            'json' => is_string($raw) ? json_decode($raw, true) : null,
        ];
    }
}
