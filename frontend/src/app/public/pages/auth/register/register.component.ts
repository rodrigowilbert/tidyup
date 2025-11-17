import { Component, OnInit, OnDestroy } from '@angular/core';
import {
  AbstractControl,
  FormBuilder,
  FormGroup,
  ValidationErrors,
  ValidatorFn,
  Validators,
} from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';
import { Router } from '@angular/router';
import { Subject } from 'rxjs';
import { takeUntil } from 'rxjs/operators';
import { AuthService } from 'src/app/core/services/auth.service';
import Swal from 'sweetalert2';

export const passwordMatchValidator: ValidatorFn = (
  control: AbstractControl
): ValidationErrors | null => {
  const password = control.get('password');
  const password_confirmation = control.get('password_confirmation');

  if (
    password &&
    password_confirmation &&
    password.value !== password_confirmation.value
  ) {
    return { passwordMismatch: true };
  }

  return null;
};

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.scss'],
})
export class RegisterComponent implements OnInit, OnDestroy {
  registerForm!: FormGroup;
  errors: any = {};
  successMessage: string | null = null;
  showPassword = false;
  showPasswordConfirmation = false;
  private destroy$ = new Subject<void>();

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.registerForm = this.fb.group(
      {
        name: ['', [Validators.required]],
        email: ['', [Validators.required, Validators.email]],
        password: ['', [Validators.required, Validators.minLength(8)]],
        password_confirmation: ['', [Validators.required]],
      },
      { validators: passwordMatchValidator }
    );
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  get name() {
    return this.registerForm.get('name');
  }
  get email() {
    return this.registerForm.get('email');
  }
  get password() {
    return this.registerForm.get('password');
  }
  get password_confirmation() {
    return this.registerForm.get('password_confirmation');
  }

  toggleShowPassword(): void {
    this.showPassword = !this.showPassword;
  }

  toggleShowPasswordConfirmation(): void {
    this.showPasswordConfirmation = !this.showPasswordConfirmation;
  }

  onRegister(): void {
    this.errors = {};
    this.successMessage = null;

    if (this.registerForm.invalid) {
      this.registerForm.markAllAsTouched();
      return;
    }

    const { password_confirmation, ...payload } = this.registerForm.value;

    this.authService
      .register(payload)
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (response) => {
          this.successMessage = response.message;
          Swal.fire({
            icon: 'success',
            title: 'Sucesso!',
            text: this.successMessage,
            showConfirmButton: false,
            timer: 2000,
          });
          this.router.navigate(['/autenticacao/acessar']);
        },
        error: (error: HttpErrorResponse) => {
          if (error.status === 422 && error.error && error.error.errors) {
            this.errors = error.error.errors;
            if (this.errors.general) {
              Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: this.errors.general,
              });
            }
          } else if (error.error && error.error.message) {
            Swal.fire({
              icon: 'error',
              title: 'Erro!',
              text: error.error.message,
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Erro!',
              text: 'Ocorreu um erro inesperado durante o registro. Tente novamente.',
            });
          }
          console.error('Erro no registro:', error);
        },
      });
  }
}
