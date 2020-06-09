const { Component } = wp.element;

import Grid from '@material-ui/core/Grid';
import Button from '@material-ui/core/Button';


class Codes extends Component {
    myStyles = {
        marginBottom: '5px'
    }

    handleButton = (e) => {
        e.preventDefault();
        this.props.handleButton(e.target.textContent);
    }

    render() {
        return (
            <div>
                <Grid container direction="column" justify="flex-start" alignItems="center">
                <h2>Codes:</h2>
                {this.props.codes.map((code, i) => (
                    <Button
                        variant={this.props.state.clicked==i ? "contained" : "outlined"}
                        style={this.myStyles}
                        onClick={this.handleButton}
                    >
                        {i}. {code}
                    </Button>
                ))}
                </Grid>
            </div>
        );
    }
}

export default Codes