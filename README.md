**PixWave** is a scalable and high-performance digital wallet microservice designed to handle real-time payments via **PIX**, using a multi-gateway architecture. The system integrates multiple payment gateways, including **Mercado Pago** and **Gerencianet**, to ensure reliability and seamless fallback capabilities. By dynamically selecting the most available gateway at any given moment, PixWave minimizes downtime and ensures continuous payment processing.

The architecture is designed to be resilient, modular, and scalable, utilizing **Docker** for containerization and **Kubernetes** for orchestration. It also employs **Redis** for caching, **SNS** for asynchronous messaging, and **Logstash + Elasticsearch** for centralized logging and monitoring, providing real-time visibility into system performance and health.

### Key Features
- **Multi-gateway architecture** with real-time gateway selection and automatic fallback.
- **Asynchronous job processing** for handling time-consuming tasks like payment processing, with retry mechanisms for failed transactions.
- **Centralized logging** and real-time monitoring using the ELK stack (Elasticsearch, Logstash, Kibana).
- **Scalability and resilience** powered by Docker and Kubernetes, ensuring the system can handle high loads and remain operational even under stress.

---

## System Architecture

### 1. **Microservice-Based Architecture**

PixWave follows a **microservice-based** architecture where the core components, such as payment processing, user management, and transaction monitoring, are decoupled into independent services. This design enables individual services to be scaled independently and maintained without affecting the entire system.

- **Laravel** is the framework used to develop the primary microservice responsible for handling payment transactions, user interactions, and API requests.
- **Docker** is employed to containerize each service, ensuring consistency across development, testing, and production environments.
- **Kubernetes** orchestrates the deployment and management of these containers, providing features like automatic scaling, self-healing, and load balancing.

### 2. **Payment Processing**

At the core of PixWave is its **payment processing module**, which is designed to handle real-time PIX transactions with high availability and fault tolerance. The system integrates two main payment gateways—**Mercado Pago** and **Gerencianet**—and utilizes a smart **gateway selection** mechanism to choose the most available gateway in real-time. 

#### 2.1. **Multi-Gateway System with Fallback**
- The system first attempts to process payments through the primary gateway, which may be **Mercado Pago** or **Gerencianet**, depending on real-time availability.
- **Redis** stores the availability status of each gateway, allowing the system to quickly retrieve and act on this information without the overhead of repeated API calls.
- If the primary gateway is unavailable or returns a failure, the system immediately falls back to the secondary gateway, ensuring minimal service interruption.

#### 2.2. **Transaction Management**
- Each payment transaction is tracked in **MySQL**, which stores details like the transaction amount, user ID, gateway used, status (success, failed, or pending), and timestamps.
- **Enum-based status management** is used to map and standardize transaction statuses across different gateways, while ensuring consistency in the database.
- **Job queues** handle the actual payment processing asynchronously, allowing users to submit payment requests without waiting for the full process to complete.

### 3. **Job and Retry Mechanisms**

The **Job system** in PixWave is crucial for handling tasks like payment processing and retries. The system ensures that time-consuming operations are moved to background jobs, providing a non-blocking experience for users and improving overall system responsiveness.

#### 3.1. **Job Handling**
- When a payment request is received, a job is dispatched to process the transaction asynchronously. This job communicates with the appropriate payment gateway to submit the transaction and receive the result.
- Jobs are dispatched in real-time and processed in parallel, ensuring that payments are handled efficiently, even under high load.

#### 3.2. **Retry Mechanism**
- If a payment attempt fails due to gateway issues or network errors, the system employs a **retry mechanism**. Each job can retry up to 3 times before marking the transaction as failed.
- In cases where all retry attempts fail, the system automatically switches to the fallback gateway and reattempts the payment.
- If the payment fails after all retry attempts and gateway fallback, the transaction is logged as failed, and alerts may be sent via **SNS**.

### 4. **Caching**

PixWave uses **Redis** for caching frequently accessed data, ensuring optimal performance and reducing unnecessary database queries.

- **Gateway availability**: The status of each payment gateway is cached in Redis, allowing the system to quickly decide which gateway to use.
- **Transaction lookups**: Recently processed transactions are also cached, enabling fast lookup without hitting the database.

### 5. **Centralized Logging and Monitoring**

**Logstash** and **Elasticsearch** are used for centralized logging, which provides real-time insight into system events, errors, and performance metrics.

- **Logstash** collects logs from all services (Laravel, job queues, payment gateways) and forwards them to Elasticsearch for indexing.
- **Elasticsearch** provides powerful querying capabilities, allowing developers and administrators to monitor the system's health and troubleshoot issues.
- **Kibana** provides a visual dashboard to explore the logs and gain insights into system behavior, errors, and performance.

### 6. **Message Queue and Asynchronous Notifications**

PixWave uses **SNS (Simple Notification Service)** for asynchronous notifications and messaging between microservices and external systems.

- When a transaction is processed, notifications are sent to external systems (e.g., user apps or third-party services) via SNS, ensuring that the system remains responsive and scalable under heavy traffic.
- **Internal messaging** is handled through a combination of **SQL** and **SNS**, ensuring that different parts of the system can communicate effectively without direct coupling.

---

## Technologies Used

### **1. Backend Framework**
- **Laravel**: The primary backend framework used for developing the payment processing and wallet logic.

### **2. Database**
- **MySQL**: A relational database that stores critical application data, including transaction details, user data, and gateway information.

### **3. Cache**
- **Redis**: Used to store temporary and frequently accessed data, such as gateway availability and recent transactions, optimizing response times and reducing load on the primary database.

### **4. Messaging**
- **SNS (Simple Notification Service)**: Used for asynchronous messaging between services and external systems, such as sending payment status notifications.

### **5. Job Queue**
- **Laravel Jobs**: Handles background job processing, including payment processing, retry logic, and transaction management.

### **6. Containerization and Orchestration**
- **Docker**: Used to containerize the application and its services, ensuring consistency across different environments.
- **Kubernetes**: Manages the orchestration, scaling, and health of Docker containers, ensuring that the system can handle increased traffic and automatically recover from failures.

### **7. Logging and Monitoring**
- **Logstash & Elasticsearch**: Centralized logging and monitoring system that collects and indexes logs from all services for real-time analysis and troubleshooting.

---

PixWave is designed with reliability, scalability, and performance in mind. Its architecture leverages microservices, background jobs, a multi-gateway payment system, and robust caching to ensure high availability and efficiency. By integrating tools like Redis, Docker, Kubernetes, and the ELK stack, PixWave provides a resilient, fault-tolerant platform for processing large volumes of PIX payments while maintaining operational transparency and performance monitoring.

