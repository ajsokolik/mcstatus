# Simple Status Page for a Self Hosted Minecraft Servers
This image is a simple auto-refreshing page that leverages the Minecraft APIs to display the status of your server. You will just need to provide your server name as an environment variable(s). There is support for multiple servers and if you are using non-standard ports, you can specify the port after the hostname with the standard hostname:port naming.

The images are available at:
- asokolik/mcstatus:latest
- ghrc.io/ajsokolik/mcstatus:latest

# Environment Variables
The image only relies on two sets of environment variables:
- MINECRAFT_SERVER?
- JAVA_MINECRAFT_SERVER?
Where the "?" is replaced with 1 - 9, to support multiple servers. The values can be any combination of the following (as seen in the examples below)
- Hostname (minecraft.example.com)
- Hostname:Port (minecraft.example.com:12345)
- IP Address: (192.0.2.1)
- IP Address:Port (192.0.2.1:12345)(minecraft.example.com:12345)

# Example Configurations
## Docker
Specify it in your Docker compose file.
```
environment:
    MINECRAFT_SERVER1: your.server.hostname
    MINECRAFT_SERVER2: your.other.server.hostname:12345
    MINECRAFT_SERVER3: 192.0.2.1
    MINECRAFT_SERVER4: 192.0.2.1:12345
    ...
    MINECRAFT_SERVER9: lots.of.server.hostname
    JAVA_MINECRAFT_SERVER1: your.java.server.hostname
    JAVA_MINECRAFT_SERVER2: your.other.java.server.hostname:54321
    JAVA_MINECRAFT_SERVER3: 192.0.2.1
    JAVA_MINECRAFT_SERVER4: 192.0.2.1:54321
    ...
    JAVA_MINECRAFT_SERVER9: lots.of.java.server.hostname
```
Alternately, you could specify this at the command line with the following.
```
-e MINECRAFT_SERVER1=your.server.hostname
```
## Kubernetes
For Kubernetes you will need to provide a Config Map, a Deployment, and a Service. Optionally you may also want to provide an ingress.
### 01-mcstatus-configmap.yaml

```
apiVersion: v1
kind: ConfigMap
metadata:
  name: mcstatus-mcserver
data:
  MINECRAFT_SERVER1: your.server.hostname
  MINECRAFT_SERVER2: your.other.server.hostname:12345
  MINECRAFT_SERVER3: 192.0.2.1
  MINECRAFT_SERVER4: 192.0.2.1:12345
  ...
  MINECRAFT_SERVER9: lots.of.server.hostname
  JAVA_MINECRAFT_SERVER1: your.java.server.hostname
  JAVA_MINECRAFT_SERVER2: your.other.java.server.hostname:54321
  JAVA_MINECRAFT_SERVER3: 192.0.2.1
  JAVA_MINECRAFT_SERVER4: 192.0.2.1:54321
  ...
  JAVA_MINECRAFT_SERVER9: lots.of.java.server.hostname
```
### 02-mcstatus-deploy.yaml
```
apiVersion: apps/v1
kind: Deployment
metadata:
  name: mcstatus
  labels:
    app: mcstatus
    layer: frontend
spec:
  replicas: 1
  selector:
    matchLabels:
      app: mcstatus
  template:
    metadata:
      labels:
        app: mcstatus
    spec:
      containers:
        - name: mcstatus
          image: ghrc.io/ajsokolik/mcstatus:latest
          imagePullPolicy: Always
          ports:
            - containerPort: 80
         envFrom:
            - configMapRef:
                name: mcstatus-mcserver
```
### 03-mcstatus-service.yaml
```
kind: Service
apiVersion: v1
metadata:
  name: mcstatus
  labels:
    app: mcstatus
    layer: frontend
spec:
  type: ClusterIP
  selector:
    app: mcstatus
  ports:
    - protocol: TCP
      port: 80
      targetPort: 80
```
### 04-mcstatus-service.yaml
```
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: mcstatus
  annotations:
    cert-manager.io/cluster-issuer: 'Your-Cluster-Issuer'
spec:
  ingressClassName: nginx
  rules:
    - host: 'mcstatus.example.com'
      http:
        paths:
        - path: /
          pathType: Prefix
          backend:
            service:
              name: mcstatus
              port:
                number: 80
  tls:
    - hosts:
      - mcstatus.example.com
      secretName: mcstatus-ext-tls
```
These files can then be applied to your cluser with
```
kubectl apply -f 01-mcstatus-configmap.yaml\
              -f 02-mcstatus-deploy.yaml\
              -f 03-mcstatus-service.yaml\
              -f 04-mcstatus-ingress.yaml
```
        
