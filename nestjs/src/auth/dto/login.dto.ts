
import {
  IsEmail,
  IsNotEmpty,
  IsString,
} from 'class-validator';

import { ApiProperty } from '@nestjs/swagger';

export class LoginDto {

  @ApiProperty({
    description: 'Correo electrónico del usuario.',
    format: 'email',
  })
  @IsNotEmpty({
    message: 'El correo es obligatorio.',
  })
  @IsEmail(
    {},
    {
      message: 'El correo debe tener un formato válido.',
    },
  )
  correo: string;

  @ApiProperty({
    description: 'Contraseña del usuario.',
    format: 'password',
  })
  @IsNotEmpty({
    message: 'La contraseña es obligatoria.',
  })
  @IsString({
    message: 'La contraseña debe ser texto.',
  })
  contrasena: string;
}
