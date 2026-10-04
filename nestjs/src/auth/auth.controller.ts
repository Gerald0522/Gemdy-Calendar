import { Body, Controller, Post, HttpCode, HttpStatus, } from '@nestjs/common';

import { AuthService } from './auth.service';
import { LoginDto } from './dto/login.dto';

@Controller()
export class AuthController {
  constructor(
    private readonly authService: AuthService,
  ) {}

  @Post('login')
  @HttpCode(HttpStatus.OK)
  async login(
    @Body() datos: LoginDto,
  ) {
    return this.authService.login(datos);
  }
}