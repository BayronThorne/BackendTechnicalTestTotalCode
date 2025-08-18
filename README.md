# Total ERP – Full Stack Case

Sistema construido con:
- **Backend:** PHP 8  
- **Base de datos:** MySQL 8  
- **Frontend:** HTML + JS + CSS  
- **Orquestación:** Docker y Docker Compose  

---

# Instrucciones en Español

## Requisitos previos

Antes de empezar, asegúrate de tener instalado en tu máquina:
- [Docker Desktop](https://www.docker.com/products/docker-desktop/)  
- [Git](https://git-scm.com/)  

No necesitas instalar PHP ni MySQL manualmente. Todo se levanta con Docker.

---

## Instalación paso a paso

1. Clonar el repositorio  
   ```bash
   git clone <este-repo>
   cd TOTAL-ER-DOCKER2/docker
   ```

2. Levantar todos los servicios (base de datos, backend, frontend y phpMyAdmin):  
   ```bash
   docker-compose up -d
   ```

3. Verificar que los contenedores estén corriendo:  
   ```bash
   docker ps
   ```
   Debes ver:
   - totalcode_db (MySQL)  
   - totalcode_backend (PHP API)  
   - totalcode_frontend (Nginx + HTML/JS)  
   - totalcode_phpmyadmin (phpMyAdmin UI)  

4. Base de datos inicial  
   - Se crea automáticamente totalcode con datos de prueba.  
   - Los scripts están en docker/mysql/init/.  

5. Accesos principales  
   - Frontend: http://localhost:8081  
   - Backend/API: http://localhost:8080/index.php/api/orders/summary?month=10&status=0  
   - phpMyAdmin: http://localhost:8082  
     - Usuario: root  
     - Contraseña: root  

---

## Uso paso a paso

1. Entra al Frontend:  
   http://localhost:8081  

2. Selecciona Mes y Estado en los filtros:  
   - Mes = Octubre, Estado = ABIERTAS  
   - Se mostrará la lista de clientes, número de órdenes y montos.  

3. Al cambiar filtros:
   - El Frontend (JS) llama al Backend (PHP API).  
   - El Backend consulta MySQL.  
   - Se devuelven los resultados en formato JSON.  
   - El Frontend renderiza la tabla con los datos.  

---

## API

Endpoint principal:  
```
GET /api/orders/summary?month=<1..12|all>&status=<0|3|4|all>
```

### Parámetros
- month → filtra por mes de la orden (1=Enero … 12=Diciembre, o all)  
- status → estado de la orden:  
  - 0 = ABIERTAS  
  - 3 = ENVIADAS  
  - 4 = ENTREGADAS  
  - all = todas  

### Ejemplo de respuesta JSON
```json
{
  "filters": {"month": 10, "status": 0},
  "rows": [
    {"client_name": "JUAN PÉREZ", "email": "juan@correo.com", "orders_count": 6, "total_amount": 1500000}
  ],
  "totals": {"orders_count": 21, "total_amount": 1995000}
}
```

---

# Instructions in English

## Requirements
- Docker Desktop  
- Git  

You don’t need to manually install PHP or MySQL, everything runs in Docker.

---

## Step-by-step installation

1. Clone the repository:
   ```bash
   git clone <this-repo>
   cd TOTAL-ER-DOCKER2/docker
   ```

2. Start the services:
   ```bash
   docker-compose up -d
   ```

3. Verify containers are running:
   ```bash
   docker ps
   ```

4. Access points:
   - Frontend: http://localhost:8081  
   - Backend/API: http://localhost:8080/index.php/api/orders/summary?month=10&status=0  
   - phpMyAdmin: http://localhost:8082  
     - User: root  
     - Password: root  

---

## API

Main endpoint:  
```
GET /api/orders/summary?month=<1..12|all>&status=<0|3|4|all>
```

### Params
- month → filter by order month  
- status →  
  - 0 = OPEN  
  - 3 = SHIPPED  
  - 4 = DELIVERED  
  - all = all  

### Sample JSON response
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

## Autor
Prueba técnica – TotalCode ERP
