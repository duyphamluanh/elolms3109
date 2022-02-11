[![Build Status](https://travis-ci.org/danielneis/moodle-block_elo_transfer_course_grade_formula.svg?branch=master)](https://travis-ci.org/danielneis/moodle-block_elo_transfer_course_grade_formula)
#  elolms-2021-dichchuyen-cn16
Chức năng: Hỗ trợ dịch chuyển idnumber của môn học gốc sang môn học clone.  
Tên plugin : Elo: Dịch chuyển công thức tính điểm các môn học

**Các bước cài đặt dành cho Tổ IT:**    
1. Cài đặt plugin.
2. Hiển thị plugin trên dashboard.
3. Cách sử dụng:  
  &nbsp;&nbsp;- Chọn môn học gốc.     
  &nbsp;&nbsp;- Chọn các môn học cần dịch chuyển.   
  &nbsp;&nbsp;- Nhấn nút dịch chuyển.   
4. **Lưu ý**:  
  &nbsp;&nbsp;- Môn học gốc có cùng công thức với môn học dịch chuyển thì mới cập nhật lại id.  
  &nbsp;&nbsp;- Khi dịch chuyển xong sẽ hiển thị link các môn đã dịch chuyển để kiểm tra lại công thức.   
  &nbsp;&nbsp;- Các môn học dịch chuyển chưa có công thức thì sẽ được cập nhật công thức dựa trên môn học gốc nếu môn học dịch chuyển có các hoạt động giống môn học gốc.     
  &nbsp;&nbsp;- Các trường hợp không dịch chuyển bao gồm:      
    &nbsp;&nbsp;&nbsp;&nbsp;+&nbsp;&nbsp;Không cùng mã lớp học. VD: FINA4317 <> ACCO2402, FINA4317 <> FINA4318  
    &nbsp;&nbsp;&nbsp;&nbsp;+&nbsp;&nbsp;Cùng mã lớp học, môn dịch chuyển có công thức nhưng không trùng với môn học gốc.    
    &nbsp;&nbsp;&nbsp;&nbsp;+&nbsp;&nbsp;Cùng mã lớp học, môn học dịch chuyển chưa có công thức nhưng không trùng các hoạt động.
 5. Một số hình ảnh:  
 ![Screenshot from 2021-10-19 17-04-40](https://user-images.githubusercontent.com/32034702/137889431-b44a6e10-f619-4714-8a5d-e88b3a1b2973.png)
 ![Screenshot from 2022-01-12 15-20-38](https://user-images.githubusercontent.com/32034702/149090602-2233e7b6-3d1f-4d8e-a0cd-e4577f21c8dc.png)
 ![Screenshot from 2021-06-04 15-51-42](https://user-images.githubusercontent.com/32034702/120774765-cb10e300-c54c-11eb-84bd-28e5ac3b4428.png)  
    
**Tài liệu hướng dẫn:** https://docs.google.com/document/d/1EOn9Lb_I5ZtDx2NDqr9KvHC7u03yWNiUlCh_lREOtZI/edit?usp=sharing
