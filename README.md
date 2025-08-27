# Simple Status Page for a Self Hosted Minecraft Servers
This image is a simple single page that leverages the Minecraft APIs to display the status of your server. You will just need to provide your server name as an environment variable(s). There is support for up to 10 server names named MINECRAFT_SERVER1 through MINECRAFT_SERVER10. If using a port other that 19132, you can specify the port after the hostname with the standard hostname:port naming.

The images are available at:
- asokolik/mcstatus:latest
- ghrc.io/ajsokolik/mcstatus:latest

## Docker
Specify it in your Docker compose file.
```
environment:
    MINECRAFT_SERVER: your.server.hostname
    MINECRAFT_SERVER1: your.other.server.hostname:19134
    MINECRAFT_SERVER2: your.other.server.hostname:19136
    ...
   MINECRAFT_SERVER2: your.other.server.hostname
```
Alternately, you could specify this at the command line with the following.
```
-e MINECRAFT_SERVER1=your.server.hostname
```
## Kubernetes
For Kubernetes you will need to provide a Config Map, a Deployment, and of course a Service. 
### 01-mcstatus-configmap.yaml

```
apiVersion: v1
kind: ConfigMap
metadata:
  name: mcstatus-mcserver
data:
  MINECRAFT_SERVER1: your.server.hostname
  MINECRAFT_SERVER2: your.other.server.hostname
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
          image: asokolik/mcstatus:latest
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
  type: NodePort
  selector:
    app: mcstatus
  ports:
    - nodePort: 30000
      port: 80
      targetPort: 80
```
These files can then be applied to your cluser with
```
kubectl apply -f 01-mcstatus-configmap.yaml\
              -f 02-mcstatus-deploy.yaml\
              -f 03-mcstatus-service.yaml
```
        
