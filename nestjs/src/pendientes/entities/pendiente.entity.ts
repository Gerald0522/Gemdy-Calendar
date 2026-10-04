import {
  Entity,
  PrimaryGeneratedColumn,
  Column,
  ManyToOne,
  JoinColumn
} from 'typeorm';

import{Curso} from '../../cursos/entities/curso.entity';

@Entity('pendientes')
export class Pendiente {

  @PrimaryGeneratedColumn()
  id: number;

  @Column({ type: 'integer' })
  usuario_id: number;

  @Column({ type: 'integer', nullable: true })
  curso_id: number | null;

  @Column({ type: 'varchar', length: 150 })
  titulo: string;

  @Column({ type: 'text', nullable: true })
  descripcion: string | null;

  @Column({ type: 'varchar', length: 20, default: 'pendiente' })
  estado: string;

  @Column({ type: 'date', nullable: true })
  fecha_limite: string | null;

  @Column({ type: 'time', nullable: true })
  hora_pendiente: string | null;

  @ManyToOne(()=> Curso, {nullable: true})
  @JoinColumn({name: 'curso_id'})
  curso: Curso | null;
}