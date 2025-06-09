import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PageAddNewObjetComponent } from './page-add-new-objet.component';

describe('PageAddNewObjetComponent', () => {
  let component: PageAddNewObjetComponent;
  let fixture: ComponentFixture<PageAddNewObjetComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PageAddNewObjetComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PageAddNewObjetComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
