import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PageListObjetsComponent } from './page-list-objets.component';

describe('PageListObjetsComponent', () => {
  let component: PageListObjetsComponent;
  let fixture: ComponentFixture<PageListObjetsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PageListObjetsComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PageListObjetsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
