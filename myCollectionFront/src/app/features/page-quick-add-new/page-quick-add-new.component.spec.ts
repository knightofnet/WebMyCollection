import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PageQuickAddNewComponent } from './page-quick-add-new.component';

describe('PageQuickAddNewComponent', () => {
  let component: PageQuickAddNewComponent;
  let fixture: ComponentFixture<PageQuickAddNewComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PageQuickAddNewComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PageQuickAddNewComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
