
# coding: utf-8

# In[1]:


import pandas as pd
import numpy as np
import collections


# In[2]:


df1 = pd.read_csv("15_16.csv")
df1['Season'] = 2015
df2 = pd.read_csv("16_17.csv")
df2['Season'] = 2016
df3 = pd.read_csv("17_18.csv")
df3['Season'] = 2017
df4 = pd.read_csv("18_19.csv")
df4['Season'] = 2018
df5 = pd.read_csv("19_20.csv")
df5['Season'] = 2019
finaldf = pd.concat([df1, df2, df3, df4, df5], axis=0)
columns = ['Season', 'HomeTeam', 'AwayTeam', 'FTHG', 'FTAG', 'FTR', 'HTHG', 'HTAG',
           'Referee', 'HS', 'AS', 'HST', 'AST', 'HF', 'AF', 'HC', 'AC', 'HY', 'AY', 'HR',
           'AR', 'B365H', 'B365D', 'B365A']
finaldf.columns.difference(columns)
finaldf = finaldf[columns]
finaldf[1600:1610]


# In[3]:


def GetTeamFitBeforeTour(team, season, tour, depth = 5):
    if (tour <= depth):
        raise Exception('Тур должен быть больше глубины')
    teamLastMatches = finaldf[(finaldf['Season'] == season) & ((finaldf['HomeTeam'] == team) | 
                                        (finaldf['AwayTeam'] == team))][(tour-1-depth):(tour-1)]
    
    goalScored = 0
    goalAllowed = 0
    coeffToWin = 0.0
    fit = 0.0
    for i in range(len(teamLastMatches)):
        row = teamLastMatches.iloc[i]
        if (row['HomeTeam'] == team):
            goalScored += row['FTHG']
            goalAllowed += row['FTAG']
            if row['FTR'] == 'A':
                fit = fit
            if row['FTR'] == 'H':
                fit +=3.3
            if row['FTR'] == 'D':
                fit +=1
            coeffToWin += row['B365H']
                
        if (row['AwayTeam'] == team):
            goalScored += row['FTAG']
            goalAllowed += row['FTHG']
            if row['FTR'] == 'A':
                fit += 3.6
            if row['FTR'] == 'H':
                fit += 0
            if row['FTR'] == 'D':
                fit += 1
            coeffToWin += row['B365A']
    
    return [goalScored, goalAllowed, fit, coeffToWin]


# In[4]:


GetTeamFitBeforeTour('Watford', 2019, 6)


# In[5]:


def GetTourMatches(season, tour):
    seasonMatches = finaldf[finaldf['Season'] == season]
    teams = pd.Series(seasonMatches['HomeTeam']).unique()
    matches = []
    for team in teams:
        tourTeamMatch = seasonMatches[(seasonMatches['HomeTeam'] == team) | (seasonMatches['AwayTeam'] == team)][(tour-1):tour]
        tourTeamPair = ([tourTeamMatch.iloc[0]['HomeTeam'], tourTeamMatch.iloc[0]['AwayTeam']])
        matches.append(tourTeamPair)
    setTeams = set()
    copyMatches = matches[:]
    for match in matches:
        if (match[0] not in setTeams) and (match[1] not in setTeams) :
            setTeams.add(match[0])
            setTeams.add(match[1])
        else:
            copyMatches.remove(match)
    return copyMatches


# In[6]:


def GetMatchResult(season, homeTeam, awayTeam):
    result = finaldf[(finaldf['Season'] == season) & ((finaldf['HomeTeam'] == homeTeam) & 
                                        (finaldf['AwayTeam'] == awayTeam))].iloc[0][5]
    if result == 'H':
        return (-1)
    if result == 'D':
        return 0
    if result == 'A':
        return 1
    


# In[7]:


GetMatchResult(2017, 'Watford', 'Arsenal')


# In[8]:


def GetTrain(depth = 5):
    toursCount = 38
    xTrain = []
    yTrain = []
    for season in range(2015, 2020):
        seasonMatches = finaldf[finaldf['Season'] == season]
        matches = []
        if (season == 2019):
            toursCount = 10
        for tour in range(depth + 1, toursCount):
            tourMatches = GetTourMatches(season, tour)
            for match in tourMatches:
                xTrain.append(np.concatenate((GetTeamFitBeforeTour(match[0], season, tour), GetTeamFitBeforeTour(match[1], season, tour))))
                yTrain.append(GetMatchResult(season, match[0], match[1]))
    return xTrain,yTrain
            
            
        
    


# In[9]:


np.concatenate((GetTeamFitBeforeTour('Watford', 2017, 8), GetTeamFitBeforeTour('Arsenal', 2017, 8)))


# In[17]:


seasonTeams = set()
for team in df5['HomeTeam']:
    seasonTeams.add(team)

seasonTeams


# In[10]:


from sklearn.model_selection import train_test_split

X, y = GetTrain()
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.1, random_state=42)


# In[11]:


from sklearn.ensemble import RandomForestClassifier

# model = RandomForestClassifier(max_depth=4, n_estimators=100)
# model.fit(X_train, y_train)


# In[12]:


# from sklearn.metrics import accuracy_score
# accuracy_score(y_test, model.predict(X_test))


# In[13]:


# X_test


# In[230]:


modelNew = RandomForestClassifier(max_depth=4, n_estimators=100)
modelNew.fit(X, y)


# In[231]:


modelNew.predict([np.concatenate((GetTeamFitBeforeTour('Watford', 2019, 11), GetTeamFitBeforeTour('Chelsea', 2019, 11)))])


# In[14]:


from flask import Flask
from flask_restful import Api, Resource, reqparse
import random
app = Flask(__name__)
api = Api(app)


# In[18]:


class Quote(Resource):
    def get(self, team1, team2):
        if (team1 in seasonTeams) and (team2 in seasonTeams):
            return str(modelNew.predict([np.concatenate((GetTeamFitBeforeTour(team1, 2019, 11), GetTeamFitBeforeTour(team2, 2019, 11)))])[0]), 200
        else:
            return "Wrong team", 400


# In[ ]:


api.add_resource(Quote, '/', '/', "/<string:team1>_<string:team2>")
if __name__ == '__main__':
    app.run(host='127.0.0.1', debug=True, port=666)

