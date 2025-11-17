import { Component, OnInit, OnDestroy } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';
import { Subject } from 'rxjs';
import { takeUntil } from 'rxjs/operators';
import { AuthService } from 'src/app/core/services/auth.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss'],
})
export class LoginComponent implements OnInit, OnDestroy {
  loginForm!: FormGroup;
  errors: any = {};
  showPassword = false;
  private destroy$ = new Subject<void>();

  constructor(private fb: FormBuilder, private authService: AuthService) {}

  ngOnInit(): void {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required]],
    });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  get email() {
    return this.loginForm.get('email');
  }

  get password() {
    return this.loginForm.get('password');
  }

  toggleShowPassword(): void {
    this.showPassword = !this.showPassword;
  }

  onLogin(): void {
    this.errors = {};
    if (this.loginForm.invalid) {
      this.loginForm.markAllAsTouched();
      return;
    }

    this.authService
      .login(this.loginForm.value)
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: () => {
          Swal.fire({
            icon: 'success',
            title: 'Login bem-sucedido!',
            showConfirmButton: false,
            timer: 1500,
          });
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
              text: 'Ocorreu um erro inesperado. Tente novamente.',
            });
          }
          console.error('Erro no login:', error);
        },
      });
  }
}
