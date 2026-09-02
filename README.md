# ♻️ ระบบบริหารจัดการขยะชุมชน (Community Waste Management System)

นวัตกรรมเพื่อสิ่งแวดล้อมที่ยั่งยืน และการบริหารจัดการเงินปันผลอย่างเป็นธรรมในระดับชุมชน

[![Landing Page](https://img.shields.io/badge/Landing%20Page-Visit%20Website-success?style=for-the-badge&logo=google-chrome)](https://recyclecommunity.infy.click/public.php)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-blue?style=for-the-badge&logo=php)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-Database-orange?style=for-the-badge&logo=mysql)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple?style=for-the-badge&logo=bootstrap)](https://getbootstrap.com/)

🔗 **[เข้าชมเว็บไซต์หลัก (Landing Page)](https://recyclecommunity.infy.click/public.php)**

---

## 📖 เกี่ยวกับระบบ (About)
**ระบบบริหารจัดการขยะชุมชน** ถูกพัฒนาขึ้นเพื่อยกระดับการจัดการขยะในระดับชุมชน โดยเน้นการคัดแยกขยะตั้งแต่ต้นทาง และสร้างแรงจูงใจด้วยระบบเงินปันผลที่โปร่งใส ตรวจสอบได้

ระบบช่วยให้คณะกรรมการชุมชนสามารถ:
- ติดตามปริมาณขยะของแต่ละครัวเรือน
- ประเมินผลการคัดแยกขยะ
- บริหารจัดการรายได้จากการขายขยะรีไซเคิลกลับคืนสู่สมาชิกได้อย่างมีประสิทธิภาพ

---

## ✨ คุณสมบัติเด่นของระบบ (Key Features)

* **📊 Dashboard สรุปภาพรวม:** แสดงข้อมูลสถิติสำคัญ เช่น ปริมาณขยะรวม รายได้ และคะแนนเฉลี่ยของชุมชนในรูปแบบกราฟที่ดูง่าย
* **⭐ ระบบประเมินผลอัจฉริยะ:** บันทึกคะแนนการคัดแยกขยะรายบ้าน พร้อมแนบรูปถ่ายเป็นหลักฐานเพื่อความโปร่งใส
* **💰 คำนวณเงินปันผลแม่นยำ:** ระบบคำนวณส่วนแบ่งรายได้ให้สมาชิกโดยอัตโนมัติตามสัดส่วนคะแนนและปริมาณขยะ
* **📄 รายงานและส่งออกข้อมูล:** รองรับการส่งออกข้อมูลเป็น PDF และ Excel เพื่อสรุปผลรายเดือนหรือรายปี
* **📱 สแกนผ่าน QR Code:** เจ้าหน้าที่สามารถสแกน QR Code หน้าบ้านเพื่อบันทึกน้ำหนักและประเมินผลได้รวดเร็ว
* **🛡️ ระบบจัดการสิทธิ์ผู้ใช้:** แยกสิทธิ์การใช้งานชัดเจนสำหรับผู้ดูแลระบบ, คณะกรรมการ และเจ้าหน้าที่เก็บขยะ

---

## 👥 ระดับการเข้าใช้งาน (Access Roles)

| บทบาท (Role) | คำอธิบายสิทธิ์การใช้งาน |
| :--- | :--- |
| <span style="color:red">**Super Admin**</span> | เข้าถึงทุกฟังก์ชันในระบบ ตั้งค่าระบบพื้นฐาน และจัดการสมาชิกทั้งหมด |
| <span style="color:blue">**Admin**</span> | จัดการข้อมูลบ้าน สมาชิก บันทึกการขายขยะ และคำนวณเงินปันผล |
| <span style="color:green">**Collector**</span> | บันทึกคะแนนการคัดแยก (ประเมิน) และบันทึกน้ำหนักขยะรายบ้าน |
| <span style="color:teal">**Auditor**</span> | ดูรายงานภาพรวม และ Dashboard เพื่อตรวจสอบความโปร่งใส |

---

## 🚀 เริ่มต้นใช้งานเบื้องต้น (Quick Start)

1. **เข้าสู่ระบบ:** ใช้บัญชีที่ได้รับจากผู้ดูแลระบบเพื่อเข้าสู่ระบบที่หน้า Login
2. **บันทึกข้อมูลพื้นฐาน:** เพิ่มข้อมูลซอย, บ้าน และสมาชิกในชุมชนให้ครบถ้วน
3. **ประเมินผลและชั่งน้ำหนัก:** เจ้าหน้าที่ลงพื้นที่บันทึกคะแนนและน้ำหนักขยะผ่านแท็บเล็ตหรือมือถือ
4. **สรุปผลและปันผล:** เมื่อสิ้นสุดรอบการขายขยะ ผู้ดูแลระบบทำการสรุปและออกเงินปันผล

---

## 🛠️ ข้อมูลทางเทคนิค (Technical Details)

* **Technology:** PHP 8.0+, MySQL
* **UI Framework:** Bootstrap 5.3, Bootstrap Icons, Google Fonts (Prompt & Sarabun)
* **Responsive Design:** รองรับการใช้งานผ่าน Mobile, Tablet และ Desktop
* **Security:** ระบบ Login แยกสิทธิ์ และการเข้ารหัสข้อมูล
* **Database Name:** `remon_waste`
* **PWA Support:** รองรับการติดตั้งแอปพลิเคชัน (Progressive Web App) และ Service Worker

> ⚠️ **หมายเหตุ:** กรุณาตั้งค่าสิทธิ์การเขียนไฟล์ (Write Permission) ในโฟลเดอร์ `uploads/` เพื่อรองรับการจัดเก็บรูปภาพหลักฐานการประเมิน

---

## 🌐 Links
* **Landing Page / คู่มือการใช้งาน:** [https://recyclecommunity.infy.click](https://recyclecommunity.infy.click)
