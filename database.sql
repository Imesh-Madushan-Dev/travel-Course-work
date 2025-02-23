CREATE DATABASE travel_db;

USE travel_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    nic VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE guides (
    guide_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    language VARCHAR(50) NOT NULL,
    experience_years INT NOT NULL,
    daily_rate DECIMAL(10,2) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    district VARCHAR(50) NOT NULL,
    imageUrl VARCHAR(255) NOT NULL,

    rating DECIMAL(3,2) DEFAULT 5.00,
    contact_number VARCHAR(15) NOT NULL
);

CREATE TABLE vehicles (
    vehicle_id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    model VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    daily_rate DECIMAL(10,2) NOT NULL,
    capacity INT NOT NULL,
    district VARCHAR(50) NOT NULL,
    imageUrl VARCHAR(255) NOT NULL,
    ac_available BOOLEAN DEFAULT true,
    transmission VARCHAR(20) NOT NULL,

    license_plate VARCHAR(20) UNIQUE NOT NULL
);

CREATE TABLE hotels (
    hotel_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    district VARCHAR(50) NOT NULL,
    star_rating INT NOT NULL,
    description TEXT,
    imageUrl VARCHAR(255) NOT NULL,
    contact_number VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL,
    price_per_night DECIMAL(10,2) NOT NULL,
    available_rooms INT NOT NULL
);

CREATE TABLE guide_bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    guide_id INT NOT NULL,
    tour_date DATE NOT NULL,
    duration_days INT NOT NULL,
    group_size INT NOT NULL,
    total_cost DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (guide_id) REFERENCES guides(guide_id)
);

CREATE TABLE vehicle_bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    pickup_date DATE NOT NULL,
    return_date DATE NOT NULL,
    pickup_location VARCHAR(100) NOT NULL,
    total_cost DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id)
);

CREATE TABLE hotel_bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hotel_id INT NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    room_count INT NOT NULL,
    guest_count INT NOT NULL,
    total_cost DECIMAL(10,2) NOT NULL,
    booking_status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (hotel_id) REFERENCES hotels(hotel_id)
);
ALTER TABLE guides ADD COLUMN availability BOOLEAN DEFAULT true;

-- Insert dummy data
INSERT INTO guides (name, language, experience_years, daily_rate, specialization, district, imageUrl, contact_number) VALUES
('Saman Perera', 'English, Sinhala', 5, 5000.00, 'Cultural Tours', 'Kandy', 'images/guides/guide1.jpg', '+94771234567'),
('Kumar Silva', 'English, Tamil', 8, 6000.00, 'Adventure Tours', 'Colombo', 'images/guides/guide2.jpg', '+94772345678'),
('Nimal Fernando', 'English, Sinhala', 3, 4000.00, 'Wildlife Tours', 'Galle', 'images/guides/guide3.jpg', '+94773456789'),
('Priya Raj', 'English, Tamil, Hindi', 6, 5500.00, 'Historical Sites', 'Anuradhapura', 'images/guides/guide4.jpg', '+94774567890');

INSERT INTO vehicles (type, model, year, daily_rate, capacity, district, imageUrl, transmission, license_plate) VALUES
('Car', 'Toyota Corolla', 2020, 5000.00, 4, 'Colombo', 'images/vehicles/car1.jpg', 'Automatic', 'CAB-1234'),
('Van', 'Toyota HiAce', 2019, 8000.00, 8, 'Kandy', 'images/vehicles/van1.jpg', 'Manual', 'VAN-5678'),
('SUV', 'Mitsubishi Montero', 2021, 10000.00, 6, 'Galle', 'images/vehicles/suv1.jpg', 'Automatic', 'SUV-9012'),
('Mini Bus', 'Nissan Civilian', 2018, 15000.00, 15, 'Colombo', 'images/vehicles/bus1.jpg', 'Manual', 'BUS-3456');

INSERT INTO hotels (name, district, star_rating, description, imageUrl, contact_number, email, price_per_night, available_rooms) VALUES
('Kandy Hills Resort', 'Kandy', 4, 'Luxury resort with panoramic views of Kandy city', 'images/hotels/hotel1.jpg', '+94812345678', 'info@kandyhills.com', 15000.00, 20),
('Galle Fort Hotel', 'Galle', 5, 'Historic colonial hotel inside Galle Fort', 'images/hotels/hotel2.jpg', '+94912345678', 'info@galleforthotel.com', 25000.00, 15),
('Sigiriya Retreat', 'Sigiriya', 4, 'Modern hotel with views of Sigiriya Rock', 'images/hotels/hotel3.jpg', '+94662345678', 'info@sigiriyaretreat.com', 18000.00, 25),
('Ella View Resort', 'Ella', 3, 'Cozy mountain resort with scenic valley views', 'images/hotels/hotel4.jpg', '+94572345678', 'info@ellaview.com', 12000.00, 30);