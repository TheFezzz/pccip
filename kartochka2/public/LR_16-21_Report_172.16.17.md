# Лабораторная работа №16-21

По схеме сети, заданной в лабораторной работе №6, выполнить настройку
маршрутизаторов, свитчей, хабов. Настроить DNS сервер, DHCP сервер, протоколы:
http, ftp. Выполнить настройку почтового и поискового сервера. Настроить NAT.
Настройку выполнить в программе Cisco Packet Tracer.

## Подсети для каждого сегмента

### Определение подсетей:

**1. Подсеть A (40 хостов):**
Необходимое количество адресов: 40 + 2 = 42
Наименьшая степень двойки, которая больше или равна 42 — это 64 (2^6).
Маска: /26 (255.255.255.192)

**2. Подсеть B (12 хостов):**
Необходимое количество адресов: 12 + 2 = 14
Наименьшая степень двойки, которая больше или равна 14 — это 16 (2^4).
Маска: /28 (255.255.255.240)

**3. Подсеть C (10 хостов):**
Необходимое количество адресов: 10 + 2 = 12
Наименьшая степень двойки, которая больше или равна 12 — это 16 (2^4).
Маска: /28 (255.255.255.240)

**4. Подсеть D (6 хостов):**
Необходимое количество адресов: 6 + 2 = 8
Наименьшая степень двойки, которая больше или равна 8 — это 8 (2^3).
Маска: /29 (255.255.255.248)

**5. Подсеть E (5 хостов):**
Необходимое количество адресов: 5 + 2 = 7
Наименьшая степень двойки, которая больше или равна 7 — это 8 (2^3).
Маска: /29 (255.255.255.248)

### Распределение адресов:

С учётом адресов от 172.16.17.0 до 172.16.17.255:

**Подсеть A:** 172.16.17.0/26
Диапазон: 172.16.17.0 - 172.16.17.63
Шлюз: 172.16.17.1
Хост: 172.16.17.2 - 172.16.17.62

**Подсеть B:** 172.16.17.64/28
Диапазон: 172.16.17.64 - 172.16.17.79
Шлюз: 172.16.17.65
Хост: 172.16.17.66 - 172.16.17.78

**Подсеть C:** 172.16.17.80/28
Диапазон: 172.16.17.80 - 172.16.17.95
Шлюз: 172.16.17.81
Хост: 172.16.17.82 - 172.16.17.94

**Подсеть D:** 172.16.17.96/29
Диапазон: 172.16.17.96 - 172.16.17.103
Шлюз: 172.16.17.97
Хост: 172.16.17.98 - 172.16.17.102

**Подсеть E:** 172.16.17.104/29
Диапазон: 172.16.17.104 - 172.16.17.111
Шлюз: 172.16.17.105
Хост: 172.16.17.106 - 172.16.17.110

---

## Часть 1. Размещение оборудования (как перетаскивать устройства)

**Шаг 1.1.** Добавляем роутер модель 4321
**Шаг 1.2.** Добавляем коммутаторы (свитчи) модель 2960
**Шаг 1.3.** Добавляем компьютеры 8 штук
**Шаг 1.4.** Добавляем серверы 1 сервер (это будет наш универсальный
сервер), ещё 1 сервер (это будет внешний сервер для интернета)
**Шаг 1.5.** Добавляем второй роутер (для NAT) модель 4321 (это будет
роутер провайдера).

**Итого:**
- Роутеров: 2 шт.
- Коммутаторов: 6 шт.
- Компьютеров: 8 шт.
- Серверов: 2 шт.

---

## Часть 2. Подключение устройств (кабели)

**Шаг 2.1. Выбираем правильный кабель**
Выбираем Copper Straight-Through. Этот кабель используется для
соединений "разные типы устройств" (компьютер-свитч, свитч-роутер).
Выбираем Copper Cross-over. Этот кабель используется для соединений
"одинаковые типы устройств" (например: свитч-свитч).

**Шаг 2.2. Подключаем роутер к главному коммутатору**
1. Кликнуть на Router0 (первый роутер)
2. Появится меню с портами. Выбрать порт GigabitEthernet0/0/0
3. Протянуть линию к Switch0 (главный коммутатор)
4. На Switch0 выбрать порт FastEthernet0/1

**Шаг 2.3. Подключаем главный коммутатор к коммутаторам подсетей**
Теперь соединяем Switch0 с остальными пятью коммутаторами:
1. Кликнуть на Switch0 → выбрать порт FastEthernet0/2 → протянуть
к Switch1 → на Switch1 выбрать FastEthernet0/1
2. Кликнуть на Switch0 → выбрать порт FastEthernet0/3 → протянуть
к Switch2 → на Switch2 выбрать FastEthernet0/1
3. Кликнуть на Switch0 → выбрать порт FastEthernet0/4 → протянуть
к Switch3 → на Switch3 выбрать FastEthernet0/1
4. Кликнуть на Switch0 → выбрать порт FastEthernet0/5 → протянуть
к Switch4 → на Switch4 выбрать FastEthernet0/1
5. Кликнуть на Switch0 → выбрать порт FastEthernet0/6 → протянуть
к Switch5 → на Switch5 выбрать FastEthernet0/1

**Шаг 2.4. Подключаем сервер и компьютеры к коммутаторам подсетей**

Подключение к Switch1 (подсеть A):
1. Кликнуть на Switch1 → порт FastEthernet0/2 → протянуть к Server0 →
на сервере выбрать FastEthernet0
2. Кликнуть на Switch1 → порт FastEthernet0/3 → протянуть к PC0 → на
ПК выбрать FastEthernet0
3. Кликнуть на Switch1 → порт FastEthernet0/4 → протянуть к PC1 → на
ПК выбрать FastEthernet0

Подключение к Switch2 (подсеть B):
1. Кликнуть на Switch2 → порт FastEthernet0/2 → протянуть к PC2
2. Кликнуть на Switch2 → порт FastEthernet0/3 → протянуть к PC3

Подключение к Switch3 (подсеть C):
1. Кликнуть на Switch3 → порт FastEthernet0/2 → протянуть к PC4
2. Кликнуть на Switch3 → порт FastEthernet0/3 → протянуть к PC5

Подключение к Switch4 (подсеть D):
1. Кликнуть на Switch4 → порт FastEthernet0/2 → протянуть к PC6

Подключение к Switch5 (подсеть E):
1. Кликнуть на Switch5 → порт FastEthernet0/2 → протянуть к PC7

**Шаг 2.5. Подключаем внешнюю сеть (для NAT)**
1. Выбрать кабель Copper Straight-Through
2. Кликнуть на Router0 (внутренний роутер) → порт GigabitEthernet0/0/1
3. Протянуть к Router1 (роутер провайдера) → порт GigabitEthernet0/0/0
4. Снова выбрать Copper Straight-Through
5. Кликнуть на Router1 → порт GigabitEthernet0/0/1
6. Протянуть к Server1 (внешний сервер) → порт FastEthernet0

Проверка: После подключений все порты должны загореться зелёными
точками.

Рисунок 1 – Размещение оборудования и подключение устройств

---

## Часть 3. Настройка VLAN на главном коммутаторе (Switch0)

VLAN нужны, чтобы разделить трафик между разными подсетями.

**Шаг 3.1. Вход в CLI коммутатора**
1. Кликнуть на Switch0
2. В верхней части окна выбрать вкладку CLI
3. Нажать Enter, пока не появится приглашение Switch>, если его нет.

**Шаг 3.2. Ввод команд**

```
enable
configure terminal
```

Создаём VLAN:

```
vlan 10
name VLAN_A
exit

vlan 20
name VLAN_B
exit

vlan 30
name VLAN_C
exit

vlan 40
name VLAN_D
exit

vlan 50
name VLAN_E
exit
```

Настраиваем порт к роутеру (транковый — пропускает все VLAN):

```
interface fastEthernet 0/1
switchport mode trunk
switchport trunk allowed vlan 10,20,30,40,50
exit
```

Настраиваем порты к коммутаторам подсетей:

```
interface fastEthernet 0/2
switchport mode trunk
switchport trunk allowed vlan 10
exit

interface fastEthernet 0/3
switchport mode trunk
switchport trunk allowed vlan 20
exit

interface fastEthernet 0/4
switchport mode trunk
switchport trunk allowed vlan 30
exit

interface fastEthernet 0/5
switchport mode trunk
switchport trunk allowed vlan 40
exit

interface fastEthernet 0/6
switchport mode trunk
switchport trunk allowed vlan 50
exit
```

Проверяем:

```
show vlan brief
```

Рисунок 2 – Проверка VLAN

---

## Часть 4. Настройка коммутаторов подсетей

### Шаг 4.1. Настройка Switch1 (подсеть A)

```
enable
configure terminal

vlan 10
name VLAN_A
exit

interface fastEthernet 0/1
switchport mode trunk
switchport trunk allowed vlan 10
exit

interface fastEthernet 0/2
switchport mode access
switchport access vlan 10
exit

interface fastEthernet 0/3
switchport mode access
switchport access vlan 10
exit

interface fastEthernet 0/4
switchport mode access
switchport access vlan 10
exit
```

### Шаг 4.2. Настройка Switch2 (подсеть B)

```
enable
configure terminal

vlan 20
name VLAN_B
exit

interface fastEthernet 0/1
switchport mode trunk
switchport trunk allowed vlan 20
exit

interface fastEthernet 0/2
switchport mode access
switchport access vlan 20
exit

interface fastEthernet 0/3
switchport mode access
switchport access vlan 20
exit
```

### Шаг 4.3. Настройка Switch3 (подсеть C)

```
enable
configure terminal

vlan 30
name VLAN_C
exit

interface fastEthernet 0/1
switchport mode trunk
switchport trunk allowed vlan 30
exit

interface fastEthernet 0/2
switchport mode access
switchport access vlan 30
exit

interface fastEthernet 0/3
switchport mode access
switchport access vlan 30
exit
```

### Шаг 4.4. Настройка Switch4 (подсеть D)

```
enable
configure terminal

vlan 40
name VLAN_D
exit

interface fastEthernet 0/1
switchport mode trunk
switchport trunk allowed vlan 40
exit

interface fastEthernet 0/2
switchport mode access
switchport access vlan 40
exit
```

### Шаг 4.5. Настройка Switch5 (подсеть E)

```
enable
configure terminal

vlan 50
name VLAN_E
exit

interface fastEthernet 0/1
switchport mode trunk
switchport trunk allowed vlan 50
exit

interface fastEthernet 0/2
switchport mode access
switchport access vlan 50
exit
```

---

## Часть 5. Настройка роутера (Router0)

### Шаг 5.1. Вход в CLI
Кликнуть Router0 → вкладка CLI

### Шаг 5.2. Настройка подынтерфейсов для VLAN

```
enable
configure terminal

interface gigabitEthernet 0/0/0
no shutdown
exit

interface gigabitEthernet 0/0/0.10
encapsulation dot1Q 10
ip address 172.16.17.1 255.255.255.192
ip helper-address 172.16.17.2
exit

interface gigabitEthernet 0/0/0.20
encapsulation dot1Q 20
ip address 172.16.17.65 255.255.255.240
ip helper-address 172.16.17.2
exit

interface gigabitEthernet 0/0/0.30
encapsulation dot1Q 30
ip address 172.16.17.81 255.255.255.240
ip helper-address 172.16.17.2
exit

interface gigabitEthernet 0/0/0.40
encapsulation dot1Q 40
ip address 172.16.17.97 255.255.255.248
ip helper-address 172.16.17.2
exit

interface gigabitEthernet 0/0/0.50
encapsulation dot1Q 50
ip address 172.16.17.105 255.255.255.248
ip helper-address 172.16.17.2
exit
```

### Шаг 5.3. Настройка интерфейса для внешней сети

```
interface gigabitEthernet 0/0/1
ip address 100.0.0.1 255.255.255.252
no shutdown
exit
```

### Шаг 5.4. Проверка

```
show ip interface brief
```

Рисунок 3 – Проверка ip интерфейса

---

## Часть 6. Настройка сервера (Server0) — все службы на одном устройстве

### Шаг 6.1. Настройка статического IP
1. Server0 → вкладка Desktop → IP Configuration
2. Выбрать Static:
   - IP Address: 172.16.17.2
   - Subnet Mask: 255.255.255.192
   - Default Gateway: 172.16.17.1
   - DNS Server: (оставить пустым)

Рисунок 4 – Настройка статического IP

### Шаг 6.2. Настройка DNS сервера
1. Server0 → вкладка Services → DNS
2. DNS Service: On
3. Добавить записи (Name → Address → Add):
   - server.local → 172.16.17.2
   - web.local → 172.16.17.2
   - ftp.local → 172.16.17.2
   - mail.local → 172.16.17.2
4. Нажать Save

Рисунок 5 – Настройка DNS сервера

### Шаг 6.3. Настройка DHCP сервера
Services → DHCP
DHCP Service: On

**PoolA (VLAN 10):**
- Pool Name: PoolA
- Default Gateway: 172.16.17.1
- DNS Server: 172.16.17.2
- Start IP Address: 172.16.17.3
- Subnet Mask: 255.255.255.192
- Max Users: 40
- Add

**PoolB (VLAN 20):**
- Pool Name: PoolB
- Default Gateway: 172.16.17.65
- DNS Server: 172.16.17.2
- Start IP Address: 172.16.17.66
- Subnet Mask: 255.255.255.240
- Max Users: 12
- Add

**PoolC (VLAN 30):**
- Pool Name: PoolC
- Default Gateway: 172.16.17.81
- DNS Server: 172.16.17.2
- Start IP Address: 172.16.17.82
- Subnet Mask: 255.255.255.240
- Max Users: 10
- Add

**PoolD (VLAN 40):**
- Pool Name: PoolD
- Default Gateway: 172.16.17.97
- DNS Server: 172.16.17.2
- Start IP Address: 172.16.17.98
- Subnet Mask: 255.255.255.248
- Max Users: 5
- Add

**PoolE (VLAN 50):**
- Pool Name: PoolE
- Default Gateway: 172.16.17.105
- DNS Server: 172.16.17.2
- Start IP Address: 172.16.17.106
- Subnet Mask: 255.255.255.248
- Max Users: 5
- Add

Рисунок 6 – Настройка DHCP сервера

### Шаг 6.4. Настройка HTTP (Web) сервера (поисковый сервер)
- Services → HTTP
- HTTP Service: On
- Выбрать index.html, удалить текст, вставить:

```html
<html>
<body style="font-family: Arial; text-align: center; margin-top: 50px;">
<h1> ПОИСКОВАЯ СИСТЕМА</h1>
<form>
<input type="text" size="40" placeholder="Введите запрос...">
<input type="submit" value="Найти">
</form>
<p>Лабораторная работа №6</p>
<p>Сервисы:
<a href="ftp://ftp.local">FTP</a> |
<a href="mailto:user1@local">Email</a>
</p>
</body>
</html>
```

- Нажмите Save

Рисунок 7 – Настройка HTTP (Web) сервера (поисковый сервер)

### Шаг 6.5. Настройка FTP сервера
- Services → FTP
- FTP Service: On
- User Setup:
  Username: user1, Password: 123, права: Read/Write/Delete/Rename/List → Add
  Username: user2, Password: 123, права: Read → Add

Рисунок 8 – Настройка FTP сервера

### Шаг 6.6. Настройка почтового сервера (EMAIL)
- Services → EMAIL
- SMTP Service: On
- POP3 Service: On
- Domain Name: введите mail.com и нажмите кнопку Set (обязательно!)
- User Setup:
  User: user1, Password: 123 → Add
  User: user2, Password: 123 → Add

Рисунок 9 – Настройка почтового сервера (EMAIL)

---

## Часть 7. Настройка клиентских ПК (получение IP по DHCP)

### Шаг 7.1. Настройка DHCP на всех ПК
Для каждого ПК (PC0 - PC7):
1. Кликнуть на ПК → Desktop → IP Configuration
2. Выбрать DHCP
3. Нажать Renew
4. Подождать 5-10 секунд

**Результаты:**

| ПК  | Подсеть | IP            | Маска           | Шлюз         |
|-----|---------|---------------|-----------------|--------------|
| PC0 | A       | 172.16.17.3   | 255.255.255.192 | 172.16.17.1  |
| PC1 | A       | 172.16.17.4   | 255.255.255.192 | 172.16.17.1  |
| PC2 | B       | 172.16.17.66  | 255.255.255.240 | 172.16.17.65 |
| PC3 | B       | 172.16.17.67  | 255.255.255.240 | 172.16.17.65 |
| PC4 | C       | 172.16.17.82  | 255.255.255.240 | 172.16.17.81 |
| PC5 | C       | 172.16.17.83  | 255.255.255.240 | 172.16.17.81 |
| PC6 | D       | 172.16.17.98  | 255.255.255.248 | 172.16.17.97 |
| PC7 | E       | 172.16.17.106 | 255.255.255.248 | 172.16.17.105 |

Рисунок 10 – Настройка клиентских ПК

### Шаг 7.2. Проверка связи с сервером
На любом ПК (например, PC0):
- Desktop → Command Prompt
- Ввести: `ping 172.16.17.2`
- Должны быть ответы Reply from 172.16.17.2

Рисунок 10 – Проверка связи с сервером

---

## Часть 8. Настройка NAT (выход во внешнюю сеть)

### Шаг 8.1. Настройка роутера провайдера (Router1)

```
enable
configure terminal
hostname ISP

interface gigabitEthernet 0/0/0
ip address 100.0.0.2 255.255.255.252
no shutdown
exit

interface gigabitEthernet 0/0/1
ip address 8.8.8.1 255.255.255.0
no shutdown
exit
```

### Шаг 8.2. Настройка внешнего сервера (Server1)
- Server1 → Desktop → IP Configuration
- Static:
  IP Address: 8.8.8.8
  Subnet Mask: 255.255.255.0
  Default Gateway: 8.8.8.1

Рисунок 11 – Настройка внешнего сервера (Server1)

### Шаг 8.3. Настройка NAT на Router0

```
enable
configure terminal

access-list 1 permit 172.16.17.0 0.0.0.255

ip route 0.0.0.0 0.0.0.0 100.0.0.2

interface gigabitEthernet 0/0/0.10
ip nat inside
exit

interface gigabitEthernet 0/0/0.20
ip nat inside
exit

interface gigabitEthernet 0/0/0.30
ip nat inside
exit

interface gigabitEthernet 0/0/0.40
ip nat inside
exit

interface gigabitEthernet 0/0/0.50
ip nat inside
exit

interface gigabitEthernet 0/0/1
ip nat outside
exit

ip nat inside source list 1 interface gigabitEthernet 0/0/1 overload
```

### Шаг 8.4. Проверка NAT
На любом ПК (например, PC0):
- Command Prompt → `ping 8.8.8.8`
- Есть ответы — NAT работает

Рисунок 12 – Проверка NAT

- На Router0: `show ip nat translations` — увидим трансляции

Рисунок 13 – Проверка трансляций ip nat

---

## Часть 9. Проверка работы всех сервисов

### Шаг 9.1. Проверка HTTP (Web) и DNS
На любом ПК (например, PC0):
- Desktop → Web Browser
- Ввести web.local — должна открыться страница "ПОИСКОВАЯ СИСТЕМА"

Рисунок 14 – Проверка HTTP (Web) и DNS

- Ввести 172.16.17.2 — то же самое

Рисунок 15 – Проверка HTTP (Web) и DNS

### Шаг 9.2. Проверка FTP
На любом ПК:
- Command Prompt → `ftp 172.16.17.2`
- Username: user1, Password: 123
- После входа: dir, quit

Рисунок 16 – Проверка FTP

### Шаг 9.3. Проверка EMAIL

**Настройка на PC0 (отправитель):**
- Desktop → Email → Configure
- Your Name: User1
- Email Address: user1@mail.com
- Incoming Mail Server: 172.16.17.2
- Outgoing Mail Server: 172.16.17.2
- User Name: user1
- Password: 123
- Save

Рисунок 17 – Проверка EMAIL, настройка на PC0

**Настройка на PC2 (получатель):**
- Desktop → Email → Configure
- Your Name: User2
- Email Address: user2@mail.com
- Incoming Mail Server: 172.16.17.2
- Outgoing Mail Server: 172.16.17.2
- User Name: user2
- Password: 123
- Save

Рисунок 18 – Проверка EMAIL, настройка на PC2

**Отправка письма:**
1. На PC0 → Compose
2. To: user2@mail.com
3. Subject: Тест
4. Message: Привет из подсети A!
5. Send

Рисунок 19 – Отправка письма

**Получение письма:**
1. На PC2 → Receive
2. Письмо должно появиться в списке

Рисунок 20 – Получение письма
