# Import libraries
import numpy as np
import matplotlib.pyplot as plt
import pandas as pd

dataset = pd.read_csv('./Chicago_Crimes_2012_to_2017.csv', names = [
        'number',
        'id',
        'case_number',
        'date',
        'block',
        'iucr',
        'primary_type',
        'description',
        'location_description',
        'arrest',
        'domestic',
        'beat',
        'district',
        'ward',
        'community_area',
        'fbi_code',
        'x_coordinate',
        'y_coordinate',
        'year',
        'updated_on',
        'latitude',
        'longtitude',
        'location'
    ])
dataset = dataset.iloc[1:, :]

dataset_small = dataset[[
        'id',
        'primary_type',
        'description',
        'location_description',
        'x_coordinate',
        'y_coordinate',
        'latitude',
        'longtitude',
        'location',
        'year'
    ]]
dataset_small.to_csv('dataset_small.csv')
dataset_small = dataset_small.dropna(subset=['latitude', 'longtitude'])
offenses = dataset_small.loc[dataset_small['primary_type'] == 'OFFENSE INVOLVING CHILDREN']
offenses2016 = offenses.loc[offenses['year'] == 2016]

import psycopg2
conn = psycopg2.connect(host="localhost",database="project_chicago", user="postgres", password="password")
cur = conn.cursor()

for i in range(0, offenses2016.shape[0]):
    crime = offenses2016.iloc[i]
    type = crime['primary_type']
    description = crime['description']
    location = crime['location_description']
    latitude = float(crime['latitude'])
    longtitude = float(crime['longtitude'])

    query =  "INSERT INTO crimes (type, description, location, geo) VALUES (%s, %s, %s, ST_GeomFromText('POINT(%s %s)', 4326));"
    data = (type, description, location, longtitude, latitude)
    cur.execute(query, data)
    
conn.commit()
cur.close()
conn.close()