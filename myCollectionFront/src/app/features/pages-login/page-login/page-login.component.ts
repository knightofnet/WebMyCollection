import {Component, inject, OnInit} from '@angular/core';
import {FormBuilder, ReactiveFormsModule, Validators} from '@angular/forms';
import {AuthService} from '../../../core/services/auth.service';
import {ActivatedRoute, Router} from '@angular/router';

@Component({
  selector: 'app-page-login',
  imports: [
    ReactiveFormsModule
  ],
  templateUrl: './page-login.component.html',
  styleUrl: './page-login.component.scss'
})
export class PageLoginComponent implements OnInit {

  private readonly formBuilder = inject(FormBuilder);

  readonly formLogin = this.formBuilder.group({
    username: ["", [Validators.required, Validators.minLength(3), Validators.maxLength(50)]],
    codepin: ["", [
      Validators.required,
      Validators.minLength(4),
      Validators.maxLength(8),
      Validators.pattern(/^\d{4,8}$/)
    ]],
  });

  /**
   * Login state:
   * 0: Not logged in
   * 1: email sent
   * 2: Error during login
   */
  protected loginState: number = 0;

  messageSendMail?: string;

  constructor(private authService: AuthService,
              private router: Router,
              private route: ActivatedRoute) {
    // Initialize the form or any other necessary setup
  }

  ngOnInit(): void {

    const token = this.route.snapshot.paramMap.get('token');

    let redirectToParam = this.route.snapshot.queryParamMap.get('redirectTo');
    if (redirectToParam === null) {
      redirectToParam = '/list';
    }

    this.authService.isAuthenticatedAsync().subscribe(
      {
        next: (response) => {
          if (response.result) {
            this.router.navigate([redirectToParam]);
          } else {
            this.verifyToken(token);

          }
        },
        error: (error) => {
          this.verifyToken(token, redirectToParam);
        }
      });


  }

  private verifyToken(token: string | null, redirectTo?: string) {
    if (token) {
      this.authService.validateLoginToken(token).subscribe({
        next: (response) => {
          if (response.result) {
            this.loginState = 3; // Token is valid, proceed with login
            this.messageSendMail = "Token validé.";

            if (redirectTo) {
              this.router.navigate([redirectTo]);
            }

          } else {
            this.loginState = 2; // Token is invalid
            this.messageSendMail = "Token invalide ou expiré. Veuillez réessayer.";
          }
        },
        error: (error) => {
          this.loginState = 2; // Error during token validation
          this.messageSendMail = "Erreur lors de la vérification de connexion.";
        }
      });
    }
  }

  handleSubmit() {

    if (this.formLogin.invalid) {
      return;
    }

    const {username, codepin} = this.formLogin.value;

    this.authService.tryLogin(username!, codepin!).subscribe({
      next: (response) => {
        if (response.result) {
          this.loginState = 1; // Login successful
          this.messageSendMail = "Un message de confirmation a été envoyé à votre adresse e-mail.";
        } else {
          this.loginState = 2; // Error during login
          this.messageSendMail = "Login failed. Please check your credentials.";
        }
      },
      error: (error) => {
        this.loginState = 2; // Error during login
        this.messageSendMail = "An error occurred during login. Please try again later.";
      }

    });


  }
}
