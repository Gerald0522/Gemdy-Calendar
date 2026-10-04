import { Entity, PrimaryGeneratedColumn, Column, } from 'typeorm';

@Entity('usuarios')
export class Usuario {
  @PrimaryGeneratedColumn()
  id: number;

  @Column({ type: 'varchar', length: 50 })
  nombre1: string;

  @Column({ type: 'varchar', length: 50, nullable: true })
  nombre2: string | null;

  @Column({ type: 'varchar', length: 50 })
  apellido1: string;

  @Column({ type: 'varchar', length: 50, nullable: true })
  apellido2: string | null;

  @Column({ type: 'varchar', length: 100, unique: true })
  correo: string;

  @Column({ type: 'varchar', length: 20, nullable: true })
  telefono: string | null;

  @Column({ type: 'varchar' })
  contrasena: string;


}