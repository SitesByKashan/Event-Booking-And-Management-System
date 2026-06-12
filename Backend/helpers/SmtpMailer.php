<?php

class SmtpMailer
{
    private $config;
    private $socket;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function send($to, $subject, $body)
    {
        if (empty($this->config["username"]) || empty($this->config["password"])) {
            return [
                "status" => false,
                "message" => "SMTP username/password missing"
            ];
        }

        $host = $this->config["host"];
        $port = $this->config["port"];

        $this->socket = @stream_socket_client(
            "tcp://{$host}:{$port}",
            $errno,
            $errstr,
            20,
            STREAM_CLIENT_CONNECT
        );

        if (!$this->socket) {
            return [
                "status" => false,
                "message" => "SMTP connection failed: {$errstr}"
            ];
        }

        stream_set_timeout($this->socket, 20);

        try {
            $this->expect([220]);
            $this->command("EHLO localhost", [250]);
            $this->command("STARTTLS", [220]);

            if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception("TLS handshake failed");
            }

            $this->command("EHLO localhost", [250]);
            $this->command("AUTH LOGIN", [334]);
            $this->command(base64_encode($this->config["username"]), [334]);
            $this->command(base64_encode($this->config["password"]), [235]);

            $fromEmail = $this->config["from_email"] ?: $this->config["username"];
            $fromName = $this->config["from_name"] ?: "EventHub";

            $this->command("MAIL FROM:<{$fromEmail}>", [250]);
            $this->command("RCPT TO:<{$to}>", [250, 251]);
            $this->command("DATA", [354]);

            $headers = [
                "From: {$fromName} <{$fromEmail}>",
                "To: {$to}",
                "Subject: {$this->encodeHeader($subject)}",
                "MIME-Version: 1.0",
                "Content-Type: text/html; charset=UTF-8"
            ];

            $htmlBody = $this->buildTemplate($subject, $body);
            $payload = implode("\r\n", $headers) . "\r\n\r\n" . $htmlBody . "\r\n.";
            $this->command($payload, [250]);
            $this->command("QUIT", [221]);
            fclose($this->socket);

            return [
                "status" => true,
                "message" => "Email sent"
            ];
        } catch (Exception $e) {
            if (is_resource($this->socket)) {
                fclose($this->socket);
            }

            return [
                "status" => false,
                "message" => $e->getMessage()
            ];
        }
    }

    private function command($command, $expectedCodes)
    {
        fwrite($this->socket, $command . "\r\n");
        return $this->expect($expectedCodes);
    }

    private function expect($expectedCodes)
    {
        $response = "";

        while (($line = fgets($this->socket, 515)) !== false) {
            $response .= $line;

            if (isset($line[3]) && $line[3] === " ") {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);

        if (!in_array($code, $expectedCodes, true)) {
            throw new Exception("SMTP error {$code}: " . trim($response));
        }

        return $response;
    }

    private function encodeHeader($value)
    {
        return "=?UTF-8?B?" . base64_encode($value) . "?=";
    }

    private function buildTemplate($subject, $body)
    {
        $safeSubject = htmlspecialchars($subject, ENT_QUOTES, "UTF-8");
        $safeBody = nl2br(htmlspecialchars($body, ENT_QUOTES, "UTF-8"));

        return "
            <div style='font-family:Arial,sans-serif;background:#f8fafc;padding:24px;color:#0f172a;'>
                <div style='max-width:560px;margin:auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;'>
                    <div style='background:#0f172a;color:#ffffff;padding:20px 24px;'>
                        <h2 style='margin:0;font-size:22px;'>EventHub</h2>
                        <p style='margin:6px 0 0;color:#cbd5e1;font-size:13px;'>{$safeSubject}</p>
                    </div>
                    <div style='padding:24px;font-size:15px;line-height:1.65;color:#334155;'>
                        {$safeBody}
                    </div>
                    <div style='padding:16px 24px;background:#f8fafc;color:#64748b;font-size:12px;'>
                        This email was sent by EventHub.
                    </div>
                </div>
            </div>
        ";
    }
}

