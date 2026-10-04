import { IsEmail, IsNotEmpty, IsString, } from 'class-validator';

export class LoginDto {
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

  @IsNotEmpty({
    message: 'La contraseña es obligatoria.',
  })
  @IsString({
    message: 'La contraseña debe ser texto.',
  })
  contrasena: string;
}