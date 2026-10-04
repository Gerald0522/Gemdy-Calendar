import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { JwtModule } from '@nestjs/jwt';
import { JwtAuthGuard } from './jwt-auth.guard';

import { Usuario } from '../usuarios/entities/usuario.entity';
import { AuthController } from './auth.controller';
import { AuthService } from './auth.service';

@Module({
  imports: [
    TypeOrmModule.forFeature([
      Usuario,
    ]),
    JwtModule.register({
      secret: 'gemdy-calendar-secret',
    }),
  ],
  controllers: [
    AuthController,
  ],
  providers: [
    AuthService,
    JwtAuthGuard,
  ],
  exports: [
    AuthService,
    JwtModule,
    JwtAuthGuard,
  ],
})
export class AuthModule {}