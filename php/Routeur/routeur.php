<?php


require_once 'response.php';

class Router
{

    private string $method;
    private string $endpoint;

    private array $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];

    private function method(): void
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    private function endpoint(): void
    {
        $this->endpoint = $_SERVER['PATH_INFO'];
    }

    public function GET(string $route, array $args, callable $callback): void
    {
        $this->routes['GET'][$route] = [
            'args' => $args,
            'callback' => $callback
        ];
    }

    public function POST(string $route, array $args, callable $callback): void
    {
        $this->routes['POST'][$route] = [
            'args' => $args,
            'callback' => $callback
        ];
    }

    public function PUT(string $route, array $args, callable $callback): void
    {
        $this->routes['PUT'][$route] = [
            'args' => $args,
            'callback' => $callback
        ];
    }

    public function DELETE(string $route, array $args, callable $callback): void
    {
        $this->routes['DELETE'][$route] = [
            'args' => $args,
            'callback' => $callback
        ];
    }

    public function run(): void
    {
        $this->method();
        $this->endpoint();

        #error_log("═══════════ Request Received ═══════════");
        #error_log(" method : ".$this->method);
        #error_log(" endpoint : ".$this->endpoint);
        #error_log("════════════════════════════════════════");

        switch ($this->method) {
            case 'GET':
                $this->handleGET();
                break;
            case 'POST':
                $this->handlePOST();
                break;
            case 'PUT':
                $this->handlePUT();
                break;
            case 'DELETE':
                $this->handleDELETE();
                break;
            default:
                Response::HTTP405(['error' => 'Method not allowed : ' . $this->method]);
                break;
        }
    }

    private function handleGET(): void
    {
        if (isset($this->routes['GET'][$this->endpoint])) {
            $args = [];
            foreach ($this->routes['GET'][$this->endpoint]['args'] as $value) {
                if (!isset($_GET[$value])) {
                    Response::HTTP400(['error' => 'Missing argument : ' . $value]);
                    return;
                } else {
                    $args[$value] = $_GET[$value];
                }
            }
            call_user_func_array($this->routes['GET'][$this->endpoint]['callback'], $args);
        } else {
            Response::HTTP400(['error' => 'Route not found : ' . $this->endpoint]);
        }
    }

    private function handlePOST(): void
    {
        if (isset($this->routes['POST'][$this->endpoint])) {
            $args = [];
            foreach ($this->routes['POST'][$this->endpoint]['args'] as $value) {
                if (!isset($_POST[$value])) {
                    Response::HTTP400(['error' => 'Missing argument : ' . $value]);
                    return;
                } else {
                    $args[$value] = $_POST[$value];
                }
            }
            call_user_func_array($this->routes['POST'][$this->endpoint]['callback'], $args);
        } else {
            Response::HTTP400(['error' => 'Route not found : ' . $this->endpoint]);
        }
    }

    private function handlePUT(): void
    {
        if (isset($this->routes['PUT'][$this->endpoint])) {
            $args = [];
            parse_str(file_get_contents('php://input'), $_PUT);
            foreach ($this->routes['PUT'][$this->endpoint]['args'] as $value) {
                if (!isset($_PUT[$value])) {
                    Response::HTTP400(['error' => 'Missing argument : ' . $value]);
                    return;
                } else {
                    $args[$value] = $_PUT[$value];
                }
            }
            call_user_func_array($this->routes['PUT'][$this->endpoint]['callback'], $args);
        } else {
            Response::HTTP400(['error' => 'Route not found : ' . $this->endpoint]);
        }
    }

    private function handleDELETE(): void
    {
        if (isset($this->routes['DELETE'][$this->endpoint])) {
            $args = [];
            foreach ($this->routes['DELETE'][$this->endpoint]['args'] as $value) {
                if (!isset($_GET[$value])) {
                    Response::HTTP400(['error' => 'Missing argument : ' . $value]);
                    return;
                } else {
                    $args[$value] = $_GET[$value];
                }
            }
            call_user_func_array($this->routes['DELETE'][$this->endpoint]['callback'], $args);
        } else {
            Response::HTTP400(['error' => 'Route not found : ' . $this->endpoint]);
        }
    }

}