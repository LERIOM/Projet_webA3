<?php

class Response
{
    public static function HTTP200($data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        http_response_code(200);
        echo json_encode($data);
    }

    public static function HTTP201($data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        http_response_code(201);
        echo json_encode($data);
    }

    public static function HTTP400($data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        http_response_code(400);
        echo json_encode($data);
    }

    public static function HTTP401($data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        http_response_code(401);
        echo json_encode($data);
    }

    public static function HTTP403($data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        http_response_code(403);
        echo json_encode($data);
    }

    public static function HTTP404($data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        http_response_code(404);
        echo json_encode($data);
    }

    public static function HTTP405($data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        http_response_code(405);


        echo json_encode($data);
    }

    public static function HTTP418($data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        http_response_code(418);
        echo json_encode($data); // I'm a teapot
    }
}