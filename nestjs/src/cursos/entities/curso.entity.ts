import {
  Entity,
  PrimaryGeneratedColumn,
  Column,
} from 'typeorm';

@Entity('cursos')
export class Curso {

  @PrimaryGeneratedColumn()
  id: number;

  @Column({ type: 'integer' })
  usuario_id: number;

  @Column({ type: 'varchar', length: 100 })
  nombre: string;

  @Column({ type: 'varchar', length: 20 })
  codigo: string;

  @Column({ type: 'varchar', length: 20 })
  semestre: string;

  @Column({ type: 'integer' })
  creditos: number;
}