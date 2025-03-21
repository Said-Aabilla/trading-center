<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiExceptions
{
    /**
     * @var array<string, string>
     */
    public static array $handlers = [
        AuthenticationException::class => 'handleAuthenticationException',
        AuthorizationException::class => 'handleAuthorizationException',
        ValidationException::class => 'handleValidationException',
        ModelNotFoundException::class => 'handleNotFoundException',
        NotFoundHttpException::class => 'handleNotFoundException',
        MethodNotAllowedHttpException::class => 'handleMethodNotAllowedHttpException',
        LazyLoadingViolationException::class => 'handleLazyLoadingViolationException',
        ThrottleRequestsException::class => 'handleThrottleRequestsException',
        AccessDeniedHttpException::class => 'handleAccessDeniedException',
        HttpException::class => 'handleHttpException',
        QueryException::class => 'handleQueryException',
        PostTooLargeException::class => 'handlePostTooLargeException',
    ];

    public static function handleAuthenticationException(AuthenticationException $e, Request $request): JsonResponse
    {
        self::logException($e, $request, 'warning');

        return self::jsonResponse($e, Response::HTTP_UNAUTHORIZED);
    }

    public static function handleAuthorizationException(Exception $e, Request $request): JsonResponse
    {
        self::logException($e, $request, 'warning');

        return self::jsonResponse($e, Response::HTTP_FORBIDDEN);
    }

    public static function handleValidationException(ValidationException $e): JsonResponse
    {
        return self::jsonResponse($e, Response::HTTP_UNPROCESSABLE_ENTITY, $e->errors());
    }

    public static function handleNotFoundException(Exception $e, Request $request): JsonResponse
    {
        return self::jsonResponse($e, Response::HTTP_NOT_FOUND, 'Not Found '.$request->getRequestUri());
    }

    public static function handleMethodNotAllowedHttpException(Exception $e): JsonResponse
    {
        return self::jsonResponse($e, Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public static function handleLazyLoadingViolationException(LazyLoadingViolationException $e, Request $request): JsonResponse
    {
        return self::jsonResponse($e, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function handleThrottleRequestsException(ThrottleRequestsException $e, Request $request): JsonResponse
    {
        self::logException($e, $request, 'warning');

        return self::jsonResponse($e, Response::HTTP_TOO_MANY_REQUESTS, 'Too many requests, please slow down.');
    }

    public static function handleAccessDeniedException(AccessDeniedHttpException $e, Request $request): JsonResponse
    {
        self::logException($e, $request, 'warning');

        return self::jsonResponse($e, Response::HTTP_FORBIDDEN, 'Access denied.');
    }

    public static function handleHttpException(HttpException $e, Request $request): JsonResponse
    {
        self::logException($e, $request);

        return self::jsonResponse($e, $e->getStatusCode());
    }

    public static function handleQueryException(QueryException $e, Request $request): JsonResponse
    {
        self::logException($e, $request);

        return self::jsonResponse($e, Response::HTTP_INTERNAL_SERVER_ERROR, 'A database error occurred.');
    }

    public static function handlePostTooLargeException(PostTooLargeException $e, Request $request): JsonResponse
    {
        self::logException($e, $request, 'warning');

        return self::jsonResponse($e, Response::HTTP_REQUEST_ENTITY_TOO_LARGE, 'The uploaded file is too large.');
    }

    private static function logException(Exception $e, Request $request, string $level = 'error'): void
    {
        Log::$level(class_basename($e), [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);
    }

    /**
     * @param  array<string, array<int, string>>|array<int, string>|string|null  $customMessage
     */
    private static function jsonResponse(Exception $e, int $status, null|array|string $customMessage = null): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'type' => class_basename($e),
            $customMessage && is_array($customMessage) ? 'errors' : 'error' => $customMessage ?? $e->getMessage(),
        ], $status);
    }
}