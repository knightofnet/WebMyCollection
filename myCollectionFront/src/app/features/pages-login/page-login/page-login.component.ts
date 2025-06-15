import {Component, inject, OnInit} from '@angular/core';
import {FormBuilder, ReactiveFormsModule, Validators} from '@angular/forms';
import {AuthService} from '../../../core/services/auth.service';
import {ActivatedRoute} from '@angular/router';

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
              private route: ActivatedRoute) {
    // Initialize the form or any other necessary setup
  }

  ngOnInit(): void {

    const token = this.route.snapshot.paramMap.get('token');

    if (token) {
      this.authService.validateLoginToken(token).subscribe({
        next: (response) => {
          if (response.result) {
            this.loginState = 1; // Token is valid, proceed with login
            this.messageSendMail = "Token validé. Vous pouvez vous connecter.";
          } else {
            this.loginState = 2; // Token is invalid
            this.messageSendMail = "Token invalide ou expiré. Veuillez réessayer.";
          }
        },
        error: (error) => {
          this.loginState = 2; // Error during token validation
          this.messageSendMail = "Une erreur est survenue lors de la validation du token. Veuillez réessayer plus tard.";
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
