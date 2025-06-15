import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PageAaddProprietaireComponent } from './page-aadd-proprietaire.component';

describe('PageAaddProprietaireComponent', () => {
  let component: PageAaddProprietaireComponent;
  let fixture: ComponentFixture<PageAaddProprietaireComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PageAaddProprietaireComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PageAaddProprietaireComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
