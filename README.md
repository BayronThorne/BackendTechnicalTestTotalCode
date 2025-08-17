# 🚀 Total ERP – Full Stack Case

System built with **PHP (Backend)**, **MySQL (Database)** and **Frontend (HTML+JS+CSS)**, fully deployable with **Docker**.

---

#🇪🇸 Instrucciones en Español

## 📦 Requisitos
- PHP 8+  
- MySQL 8+  
- Docker y Docker Compose  

## ⚙️ Instalación con Docker

1. Clonar el proyecto:  
   ```bash
   git clone <este-repo>
   cd TOTAL-ER-DOCKER2/docker
   ```

2. Levantar servicios:  
   ```bash
   docker-compose up -d
   ```

3. La base de datos `totalcode` se crea automáticamente con datos de ejemplo desde `docker/mysql/init/`.  

4. Acceder al **Frontend**:  
   👉 [http://localhost:8081](http://localhost:8081)  

5. El **Backend/API** responde en:  
   👉 [http://localhost:8080/index.php/api/orders/summary?month=10&status=0](http://localhost:8080/index.php/api/orders/summary?month=10&status=0)  

## 🔌 API

**Endpoint principal:**  
```
GET /api/orders/summary?month=<1..12|nombre|all>&status=<0|3|4|all>
```

- **month** → filtra por `MONTH(date_placed)`  
- **status** →  
  - `0` = ABIERTAS  
  - `3` = ENVIADAS  
  - `4` = ENTREGADAS  
  - `all` = todas  

**Respuesta JSON de ejemplo:**  
```json
{
  "filters": {"month": 10, "status": 0},
  "rows": [
    {"client_name": "NOMBRE APELLIDO", "email": "x@x.com", "orders_count": 6, "total_amount": 1500000}
  ],
  "totals": {"orders_count": 21, "total_amount": 1995000}
}
```

---

# 🇬🇧 Instructions in English

## 📦 Requirements
- PHP 8+  
- MySQL 8+  
- Docker and Docker Compose  

## ⚙️ Installation with Docker

1. Clone the project:  
   ```bash
   git clone <this-repo>
   cd TOTAL-ER-DOCKER2/docker
   ```

2. Start services:  
   ```bash
   docker-compose up -d
   ```

3. The database `totalcode` will be created automatically with seed data from `docker/mysql/init/`.  

4. Access the **Frontend**:  
   👉 [http://localhost:8081](http://localhost:8081)  

5. The **Backend/API** is available at:  
   👉 [http://localhost:8080/index.php/api/orders/summary?month=10&status=0](http://localhost:8080/index.php/api/orders/summary?month=10&status=0)  

## 🔌 API

**Main endpoint:**  
```
GET /api/orders/summary?month=<1..12|name|all>&status=<0|3|4|all>
```

- **month** → filters by `MONTH(date_placed)`  
- **status** →  
  - `0` = OPEN  
  - `3` = SHIPPED  
  - `4` = DELIVERED  
  - `all` = all  

**Sample JSON response:**  
```json
{
  "filters": {"month": 10, "status": 0},
  "rows": [
    {"client_name": "JOHN DOE", "email": "john@doe.com", "orders_count": 6, "total_amount": 1500000}
  ],
  "totals": {"orders_count": 21, "total_amount": 1995000}
}
```

---

## 👤 Author / Autor
Technical practice project – **TotalCode ERP**
