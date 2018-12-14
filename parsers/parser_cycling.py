# Import libraries
import numpy as np
import matplotlib.pyplot as plt
import pandas as pd

dataset = pd.read_csv('./data.csv', names = [
        'trip_id',
        'year',
        'month',
        'week',
        'day',
        'hour',
        'usertype',
        'gender',
        'starttime',
        'stoptime',
        'tripduration',
        'temperature',
        'events',
        'from_station_id',
        'from_station_name',
        'latitude_start',
        'longitude_start',
        'dpcapacity_start',
        'to_station_id',
        'to_station_name',
        'latitude_end',
        'longitude_end',
        'dpcapacity_end'
    ])
dataset = dataset.iloc[1:, :]

stations = dataset[['from_station_name','latitude_start','longitude_start']].drop_duplicates(subset='from_station_name')
stations.to_csv('stations.csv')

import psycopg2
conn = psycopg2.connect(host="localhost",database="project_chicago", user="postgres", password="password")
cur = conn.cursor()

for i in range(0, stations.shape[0] - 1):
    station = stations.iloc[i]
    name = station['from_station_name']
    latitude = float(station['latitude_start'])
    longtitude = float(station['longitude_start'])

    query =  "INSERT INTO stations (name, geo) VALUES (%s, ST_GeomFromText('POINT(%s %s)', 4326));"
    data = (name, longtitude, latitude)
    cur.execute(query, data)
    
conn.commit()
cur.close()
conn.close()