# PixWave

**PixWave** is a scalable and high-performance digital wallet microservice designed to handle real-time payments via **PIX**, using a **multi-gateway architecture**. The system integrates multiple payment gateways, including **Mercado Pago** and **Gerencianet**, ensuring reliability and seamless fallback capabilities. By dynamically selecting the most available gateway at any given moment, PixWave minimizes downtime and ensures continuous payment processing.

The architecture is built to be **resilient, modular, and scalable**, using **Docker** for containerization, **Kubernetes** for orchestration, **Redis** for caching, **SNS** for asynchronous messaging, and the **ELK stack** (Logstash, Elasticsearch, Kibana) for centralized logging and real-time monitoring of system performance.

## 🚀 Key Features

- **Multi-gateway architecture** with real-time gateway selection and automatic fallback.
- **Asynchronous job processing** with retry mechanisms for handling failed transactions and long-running tasks.
- **Centralized logging and real-time monitoring** using the **ELK stack** (Elasticsearch, Logstash, Kibana).
- **Scalability and resilience** powered by Docker and Kubernetes, ensuring the system can handle high loads and remain operational under stress.

---

## 📊 System Architecture

### 1. **Microservice-Based Architecture**

PixWave follows a **microservice-based architecture**, where key components—such as payment processing, user management, and transaction monitoring—are decoupled into independent services. This design enables scaling individual services independently and maintaining them without affecting the entire system.

- **Laravel** is used as the backend framework to manage payment transactions, user interactions, and API requests.
- **Docker** containerizes each service, ensuring consistency across development, testing, and production environments.
- **Kubernetes** orchestrates the deployment and management of these containers, providing features like automatic scaling, self-healing, and load balancing.

### 2. **Payment Processing**

The core of PixWave is its payment processing module, designed to handle real-time PIX transactions with high availability and fault tolerance. The system integrates two primary payment gateways: **Mercado Pago** and **Gerencianet**, and uses a smart gateway selection mechanism.

#### 2.1. **Multi-Gateway System with Fallback**

- The system first attempts to process payments via the primary gateway (either Mercado Pago or Gerencianet).
- **Redis** stores the availability status of each gateway, enabling fast retrieval and decision-making.
- If the primary gateway is unavailable, the system immediately switches to the fallback gateway, ensuring continuous payment processing with minimal downtime.

#### 2.2. **Transaction Management**

- All transactions are logged in **MySQL**, including details like transaction amount, user ID, gateway used, status (success, failed, or pending), and timestamps.
- **Enum-based status management** standardizes transaction statuses across different gateways for consistency in the database.
- **Job queues** handle payment processing asynchronously, allowing users to initiate payments without waiting for the entire process to complete.

---

### 3. **Job and Retry Mechanisms**

The **Job system** in PixWave is critical for handling tasks like payment processing and retries. By dispatching jobs for payment requests asynchronously, PixWave provides a non-blocking experience for users and increases overall system responsiveness.

#### 3.1. **Job Handling**

- When a payment is requested, a **job** is dispatched to process the transaction asynchronously, contacting the relevant payment gateway to submit the transaction and retrieve the result.
- Jobs are processed in parallel, ensuring efficient handling of large volumes of payments.

#### 3.2. **Retry Mechanism**

- If a payment attempt fails due to gateway issues, the system retries up to **3 times** before marking the transaction as failed.
- After all retry attempts, the system switches to the fallback gateway and retries the payment.
- If the fallback also fails, the transaction is logged as failed, and alerts may be sent via **SNS** for further investigation.

---

### 4. **Caching with Redis**

PixWave uses **Redis** for caching to enhance performance and reduce the load on the primary database.

- **Gateway availability**: Cached in Redis, allowing the system to quickly determine which payment gateway to use without making redundant API calls.
- **Transaction lookups**: Recently processed transactions are cached, enabling fast lookup and reducing unnecessary queries to the MySQL database.

---

### 5. **Centralized Logging and Monitoring**

**Logstash** and **Elasticsearch** are used to manage centralized logging, providing real-time insights into system events, errors, and performance metrics.

- **Logstash** collects logs from all services (Laravel, job queues, payment gateways) and sends them to **Elasticsearch** for indexing.
- **Elasticsearch** enables powerful querying for system health checks and troubleshooting.
- **Kibana** offers a graphical dashboard for exploring logs, tracking errors, and visualizing system performance.

---

### 6. **Message Queue and Asynchronous Notifications**

PixWave uses **SNS (Simple Notification Service)** for messaging between microservices and for sending notifications to external systems.

- When a transaction is processed, notifications are sent to external systems (e.g., user apps or third-party services) via SNS, ensuring that the system stays responsive even under high traffic.
- Internal messaging between system components is handled via **SQL** and **SNS**, ensuring decoupled communication between services.

---

## 🛠️ Installation Instructions

### 1. **Clone the Repository**

To begin, clone the PixWave repository to your local machine:

```bash
git clone https://github.com/your-repository/pixwave.git
cd pixwave
```

### 2. **Set Up Environment Variables**

Copy the `.env.example` file to `.env` and configure your environment variables. These variables include database credentials, payment gateway credentials (Mercado Pago and Gerencianet), and API keys for other services:

```bash
cp .env.example .env
```

Update the following values in the `.env` file:

- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` – MySQL database settings.
- `MERCADOPAGO_ACCESS_TOKEN` – Mercado Pago access token.
- `GERENCIANET_CLIENT_ID`, `GERENCIANET_CLIENT_SECRET`, `GERENCIANET_PIX_KEY` – Gerencianet API credentials.
- `SNS_API_KEY` – Your SNS API key for notifications.
- `LOGSTASH_HOST` – Host for Logstash service (for centralized logging).

### 3. **Install Dependencies**

Run the following command to install all required dependencies:

```bash
composer install
```

### 4. **Set Up Docker**

Make sure you have **Docker** installed on your machine. Then, use **Docker Compose** to build and run the necessary containers:

```bash
docker-compose up --build
```

This command will start up the following services:
- **Laravel App** (PHP backend)
- **MySQL** (Relational database)
- **Redis** (Cache)
- **Elasticsearch & Logstash** (Centralized logging)
- **Kibana** (Log analysis interface)

### 5. **Run Migrations and Seeders**

After the containers are up and running, execute the migrations to set up the database schema and seed some initial data:

```bash
docker exec -it laravel_app php artisan migrate --seed
```

### 6. **Generate Application Key**

Generate the application encryption key, which is used by Laravel for secure encryption:

```bash
docker exec -it laravel_app php artisan key:generate
```

### 7. **Run the Application**

With everything set up, you can now access the application. By default, the application will be available at `http://localhost`:

```bash
docker-compose up
```

Visit `http://localhost` in your browser to interact with the PixWave system.

---

## 🤖 Troubleshooting

- **Port Conflicts**: If you encounter port conflicts (e.g., Redis or MySQL already running on your system), update the `docker-compose.yml` file to change the default port mappings.
- **Permission Errors**: If permission issues arise when trying to write to logs or storage, ensure that the `storage/` and `bootstrap/cache/` directories are writable by running:

  ```bash
  sudo chmod -R 775 storage
  sudo chmod -R 775 bootstrap/cache
  ```

- **Container Restarts**: If containers are restarting or failing to run, check the logs for detailed error messages:

  ```bash
  docker-compose logs
  ```

---

## 💻 Technologies Used

### 1. **Backend Framework**

- **Laravel**: Used to develop the core payment processing logic and API interactions.

### 2. **Database**

- **MySQL**: Relational database for storing transaction details, user data, and gateway information.

### 3. **Cache**

- **Redis**: Caching layer for optimizing response times and reducing load on the primary database.

### 4. **Messaging**

- **SNS (Simple Notification Service)**: Used for sending asynchronous notifications and internal messaging between services.

### 5. **Job Queue**

- **Laravel Jobs**: Manages background job processing, including payment transactions and retries.

### 

6. **Containerization and Orchestration**

- **Docker**: Ensures consistency across environments by containerizing the application.
- **Kubernetes**: Handles container orchestration, scaling, and health checks for services.

### 7. **Logging and Monitoring**

- **Logstash & Elasticsearch**: Collects and indexes logs for real-time monitoring and analysis.
- **Kibana**: Provides a visual interface for exploring logs and tracking system performance.
