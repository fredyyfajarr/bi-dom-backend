<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Cek apakah user sudah login DAN role-nya tidak sesuai dengan yang diizinkan
        if ($request->user() && $request->user()->role !== $role) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin (403).'
            ], 403);
        }

        return $next($request);
    }
}
