import { CanActivate, ExecutionContext, Injectable, UnauthorizedException, } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { Request } from 'express';

@Injectable()
export class JwtAuthGuard implements CanActivate {
  constructor(
    private readonly jwtService: JwtService,
  ) {}

  async canActivate(
    context: ExecutionContext,
  ): Promise<boolean> {
    const request = context
      .switchToHttp()
      .getRequest<Request & {usuario?: any}>();

    const token = this.extraerToken(request);

    if (!token) {
      throw new UnauthorizedException(
        'Token de autenticación no proporcionado.',
      );
    }

    try {
      const payload = await this.jwtService.verifyAsync(token);

      request.usuario = payload;

      return true;
    } catch {
      throw new UnauthorizedException(
        'Token de autenticación no válido.',
      );
    }
  }

  private extraerToken(
    request: Request,
  ): string | undefined {
    const authorization = request.headers.authorization;

    if (!authorization) {
      return undefined;
    }

    const [tipo, token] = authorization.split(' ');

    if (tipo !== 'Bearer' || !token) {
      return undefined;
    }

    return token;
  }
}