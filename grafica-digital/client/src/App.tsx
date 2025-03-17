import React from 'react';
import { BrowserRouter as Router, Route, Switch } from 'react-router-dom';
import Dashboard from './components/dashboard/Dashboard';
import Kanban from './components/kanban/Kanban';
import Clients from './components/clients/Clients';
import Machines from './components/machines/Machines';
import Stock from './components/stock/Stock';
import Quotes from './components/quotes/Quotes';
import Auth from './components/auth/Auth';

const App: React.FC = () => {
    return (
        <Router>
            <Switch>
                <Route path="/" exact component={Dashboard} />
                <Route path="/kanban" component={Kanban} />
                <Route path="/clients" component={Clients} />
                <Route path="/machines" component={Machines} />
                <Route path="/stock" component={Stock} />
                <Route path="/quotes" component={Quotes} />
                <Route path="/auth" component={Auth} />
            </Switch>
        </Router>
    );
};

export default App;