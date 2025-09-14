import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PhotoPayloadComponent } from './photo-payload.component';

describe('PhotoPayloadComponent', () => {
  let component: PhotoPayloadComponent;
  let fixture: ComponentFixture<PhotoPayloadComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PhotoPayloadComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PhotoPayloadComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
