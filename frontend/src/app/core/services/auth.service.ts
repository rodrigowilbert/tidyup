import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable, of } from 'rxjs';
import { tap, catchError } from 'rxjs/operators';
import { environment } from 'src/environments/environment';

interface AuthResponse {
  message: string;
  token: string;
}

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private readonly API_URL = environment.apiUrl;

  constructor(private http: HttpClient, private router: Router) {}

  register(userData: {
    name: string;
    email: string;
    password: string;
  }): Observable<{ message: string }> {
    return this.http
      .post<{ message: string }>(`${this.API_URL}/register`, userData)
      .pipe(
        tap((response) => {}),
        catchError((error) => {
          console.error('Erro de registro:', error);
          throw error;
        })
      );
  }

  login(credentials: {
    email: string;
    password: string;
  }): Observable<AuthResponse> {
    return this.http
      .post<AuthResponse>(`${this.API_URL}/login`, credentials)
      .pipe(
        tap((response) => {
          this.setToken(response.token);
          this.router.navigate(['/conta']);
        }),
        catchError((error) => {
          console.error('Erro de login:', error);
          throw error;
        })
      );
  }

  logout(): void {
    this.http.post(`${this.API_URL}/logout`, {}).subscribe({
      next: () => {
        this.removeToken();
        this.router.navigate(['/autenticacao/acessar']);
      },
      error: (error) => {
        console.error('Erro ao fazer logout no backend:', error);
        this.removeToken();
        this.router.navigate(['/autenticacao/acessar']);
      },
    });
  }

  getToken(): string | null {
    return sessionStorage.getItem(environment.tokenKey);
  }

  isLoggedIn(): boolean {
    return !!this.getToken();
  }

  private setToken(token: string): void {
    sessionStorage.setItem(environment.tokenKey, token);
  }

  private removeToken(): void {
    sessionStorage.removeItem(environment.tokenKey);
  }
}
