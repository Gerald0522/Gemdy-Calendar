import {
  ArgumentsHost,
  Catch,
  ExceptionFilter,
  HttpStatus,
} from '@nestjs/common';
import type { Response } from 'express';

import { BusinessRuleException } from '../exceptions/business-rule.exception';

@Catch(BusinessRuleException)
export class BusinessRuleExceptionFilter
  implements ExceptionFilter
{
  catch(
    exception: BusinessRuleException,
    host: ArgumentsHost,
  ) {
    const context = host.switchToHttp();
    const response =
      context.getResponse<Response>();

    response
      .status(HttpStatus.CONFLICT)
      .json({
        message: exception.message,
        error: 'Conflict',
        statusCode: HttpStatus.CONFLICT,
      });
  }
}