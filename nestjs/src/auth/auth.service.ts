import { Injectable, UnauthorizedException, } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { JwtService } from '@nestjs/jwt';
import { Repository } from 'typeorm';
import * as bcrypt from 'bcrypt';

import { Usuario } from '../usuarios/entities/usuario.entity';
import { LoginDto } from './dto/login.dto';

@Injectable()
export class AuthService {
  constructor(
    @InjectRepository(Usuario)
    private readonly usuarioRepository: Repository<Usuario>,

    private readonly jwtService: JwtService,
  ) {}

  async login(datos: LoginDto) {
    const usuario =
      await this.usuarioRepository.findOne({
        where: {
          correo: datos.correo,
        },
      });

    if (!usuario) {
      throw new UnauthorizedException(
        'Las credenciales proporcionadas no son válidas.',
      );
    }

    const HashCompatible = usuario.contrasena.startsWith('$2y$')
      ? '$2b$' + usuario.contrasena.slice(4)
      :usuario.contrasena;


      const contrasenaValida = await bcrypt.compare (
      datos.contrasena,
      HashCompatible,
    );

    if (!contrasenaValida) {
      throw new UnauthorizedException(
        'Las credenciales proporcionadas no son válidas.',
      );
    }

    const token = await this.jwtService.signAsync(
      {
        sub: usuario.id,
        correo: usuario.correo,
      
      },
      {
        expiresIn: '8h',
      },
    );

    const expiraEn = new Date(
      Date.now() + 8 * 60 * 60 * 1000,
    );

    return {
      message: 'Inicio de sesión exitoso.',
      usuario: {
        id: usuario.id,
        nombre1: usuario.nombre1,
        nombre2: usuario.nombre2,
        apellido1: usuario.apellido1,
        apellido2: usuario.apellido2,
        correo: usuario.correo,
        telefono: usuario.telefono,
      },
      token,
      token_type: 'Bearer',
      expires_at: expiraEn.toISOString(),
    };
  }
}